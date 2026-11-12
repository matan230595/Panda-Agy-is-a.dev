#!/usr/bin/env python3
"""Isolated PHP integration tests; never touch production paths."""
import json, os, pathlib, re, shutil, subprocess, tempfile, time, urllib.request, urllib.error, urllib.parse, http.cookiejar, secrets, sys
root = pathlib.Path(__file__).resolve().parents[1]
with tempfile.TemporaryDirectory(prefix="bc-admin-test-") as td:
    tmp=pathlib.Path(td)
    private=tmp/"private"
    private.mkdir()
    (private/"backups").mkdir()
    os.chmod(private,0o700)
    site=tmp/"site"
    site.mkdir()
    payload={
      "contact":{"phone":"123","email":"test@example.invalid","whatsapp":"123","github":"https://example.invalid"},
      "terminal":{"user":"test","bio":"test","skills":"test","commands":[]},
      "marketing":{k:{"title":"test","badge":"test","bio":"test","services":[]} for k in ("he","en","es")}
    }
    config=site/"config.js"
    config.write_text("window.SITE_DATA = "+json.dumps(payload)+";\n")
    admin=site/"admin"
    shutil.copytree(root/"admin",admin)
    common=admin/"common.php"
    source=common.read_text()
    old="const AUTH_DIR='/home/matanadmin/.panda-bc-admin';"
    old2="const SITE_CONFIG='/home/matanadmin/web/bc.panda-il.com/public_html/config.js';"
    assert old in source and old2 in source
    source=source.replace(old,"const AUTH_DIR="+json.dumps(str(private))+";")
    source=source.replace(old2,"const SITE_CONFIG="+json.dumps(str(config))+";")
    # HTTP loopback fixture only; production common.php remains Secure-cookie HTTPS-only.\n    source=source.replace("'secure'=>true", "'secure'=>false")\n    common.write_text(source)
    password=secrets.token_urlsafe(25)
    hashed=subprocess.check_output(["php","-r","echo password_hash($argv[1], PASSWORD_DEFAULT);",password],text=True).strip()
    (private/"password.hash").write_text(hashed)
    proc=subprocess.Popen(["php","-S","127.0.0.1:18739","-t",str(site)],stdout=subprocess.DEVNULL,stderr=subprocess.PIPE)
    base="http://127.0.0.1:18739"
    jar=http.cookiejar.CookieJar()\n    opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    def request(path, data=None, headers=None):
        nonlocal_placeholder=None
        h=dict(headers or {})
        if cookie:h["Cookie"]=cookie
        req=urllib.request.Request(base+path,data=data,headers=h,method="POST" if data is not None else "GET")
        try:
            resp=opener.open(req,timeout=5)
            return resp.status, dict(resp.headers),resp.read().decode()
        except urllib.error.HTTPError as e:
            return e.code,dict(e.headers),e.read().decode()
    try:
        for _ in range(40):
            try:
                request("/admin/login.php")
                break
            except Exception:
                time.sleep(.1)
        else:raise RuntimeError("PHP local test server unavailable")
        status,h,body=request("/admin/api.php")
        assert status==401,("unauthenticated API",status)
        status,h,body=request("/admin/login.php")
        assert status==200
        match=re.search(r'name="csrf" value="([a-f0-9]+)"',body)
        assert match,"CSRF token missing"
        csrf=match.group(1)
        cookie=h["Set-Cookie"].split(";",1)[0]
        wrong=urllib.parse.urlencode({"csrf":csrf,"password":"incorrect"}).encode()
        status,_,_=request("/admin/login.php",wrong,{"Content-Type":"application/x-www-form-urlencoded"})
        assert status==200,("invalid login",status)
        good=urllib.parse.urlencode({"csrf":csrf,"password":password}).encode()
        status,h,_=request("/admin/login.php",good,{"Content-Type":"application/x-www-form-urlencoded"})
        assert status in (200,303),("valid login",status)
        if "Set-Cookie" in h:cookie=h["Set-Cookie"].split(";",1)[0]
        status,_,body=request("/admin/api.php")
        assert status==200,("authenticated API",status,body)
        info=json.loads(body)
        revision=info["revision"]
        csrf=info["csrf"]
        data=json.dumps({"revision":revision,"data":payload}).encode()
        status,_,body=request("/admin/api.php",data,{"Content-Type":"application/json","X-CSRF-Token":"wrong"})
        assert status==403,("CSRF refusal",status)
        status,_,body=request("/admin/api.php",data,{"Content-Type":"application/json","X-CSRF-Token":csrf})
        assert status==200,("save",status,body)
        saved=json.loads(body)
        assert saved["saved"] is True
        assert len(list((private/"backups").glob("config-*.js")))==1
        status,_,body=request("/admin/api.php",data,{"Content-Type":"application/json","X-CSRF-Token":csrf})
        assert status==409,("conflict",status,body)
        status,_,_=request("/admin/index.php")
        assert status==200,"editor"
        print("RESULT: LOGIN_CSRF_SAVE_CONFLICT_BACKUP_OK")
    finally:
        proc.terminate()
        try:proc.wait(timeout=3)
        except subprocess.TimeoutExpired:proc.kill()
