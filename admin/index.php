<?php
declare(strict_types=1);
require __DIR__.'/common.php';
start_admin();
if(empty($_SESSION['authenticated']) || time()-(int)($_SESSION['last']??0)>1800){
 header('Location: login.php',true,303);exit;
}
$_SESSION['last']=time();
token();
?>
<!doctype html>
<html lang="he" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="color-scheme" content="dark">
<title>BC Admin v2 | Control Center</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin-v2.css?v=2">
<script src="../config.js"></script>
<script src="assets/admin-v2.js?v=2" defer></script>
</head>
<body>
<div class="shell">
<aside class="sidebar" id="sidebar" aria-label="ניווט מנהל">
 <a class="identity" href="#overview" aria-label="BC מרכז בקרה">
  <span class="monogram">BC<span class="gold">.</span></span>
  <span><strong>CONTROL CENTER</strong><small>ADMINISTRATION / V2</small></span>
 </a>
 <div class="nav-label">WORKSPACE</div>
 <nav id="nav">
  <button type="button" class="nav-link active" data-screen="overview"><span class="nav-symbol">◈</span><span>סקירה כללית</span></button>
  <button type="button" class="nav-link" data-screen="contact"><span class="nav-symbol">⌁</span><span>פרטי קשר</span></button>
  <button type="button" class="nav-link" data-screen="terminal"><span class="nav-symbol">⌘</span><span>פרופיל וטרמינל</span></button>
  <button type="button" class="nav-link" data-screen="marketing"><span class="nav-symbol">▤</span><span>Marketing OS</span></button>
  <button type="button" class="nav-link" data-screen="operations"><span class="nav-symbol">◇</span><span>מערכת וגרסאות</span></button>
 </nav>
 <div class="side-bottom"><span class="live-dot"></span><span>PRIVATE ADMIN AREA</span><small>bc.panda-il.com</small></div>
</aside>
<div class="main-wrap">
<header class="topbar">
 <div class="top-left"><button class="icon-btn menu-toggle" type="button" id="menuToggle" aria-label="פתיחת ניווט">☰</button><span class="crumb">BC <span>/</span> <strong id="currentCrumb">סקירה כללית</strong></span></div>
 <div class="top-actions"><span class="environment"><span class="live-dot"></span>PRODUCTION</span><a href="../" target="_blank" rel="noopener noreferrer" class="btn ghost compact">↗ האתר</a><a href="../marketing.html" target="_blank" rel="noopener noreferrer" class="btn ghost compact">↗ שיווק</a><form method="post" action="logout.php" id="logoutForm"><input type="hidden" name="csrf" value="<?=htmlspecialchars(token(),ENT_QUOTES)?>"><button class="btn ghost compact" type="submit">התנתק</button></form></div>
</header>
<main class="content" id="workspace">
 <div class="intro"><p class="eyebrow" id="eyebrow">EXECUTIVE WORKSPACE</p><h1 id="screenTitle">סקירה כללית</h1><p class="lede" id="screenDesc">תמונת מצב והגישה המהירה לניהול הנוכחות הדיגיטלית.</p></div>
 <div class="notice" id="notice" role="status" aria-live="polite" hidden></div>
 <section class="screen active" id="overview" aria-labelledby="screenTitle">
  <div class="metrics"><article class="metric"><span>DOMAIN</span><strong>bc.panda-il.com</strong><small>סביבת הייצור</small></article><article class="metric"><span>CONFIG REVISION</span><strong id="metricRevision">—</strong><small>גרסה פעילה בשרת</small></article><article class="metric"><span>EDITING STATE</span><strong id="metricChanges">טוען</strong><small>שינויים שטרם נשמרו</small></article></div>
  <div class="panel"><div class="panel-head"><div><p class="eyebrow">QUICK ACTIONS</p><h2>מה תרצה לנהל?</h2></div></div><div class="quick-grid"><button type="button" data-go="contact" class="quick-card"><span>01 / CONTACT</span><strong>פרטי קשר</strong><small>טלפון, וואטסאפ, אימייל וקישורים</small></button><button type="button" data-go="terminal" class="quick-card"><span>02 / PROFILE</span><strong>פרופיל וטרמינל</strong><small>טקסטים, יכולות ופקודות</small></button><button type="button" data-go="marketing" class="quick-card"><span>03 / MARKETING</span><strong>תוכן שיווקי</strong><small>עברית, אנגלית וספרדית</small></button></div></div>
  <div class="panel info-panel"><h2>עבודה מבוקרת</h2><p>השינויים נשמרים רק בלחיצה על ״שמור באתר״. לפני השמירה המערכת בודקת שגרסת הנתונים בשרת לא השתנתה, וה־API שומר גיבוי לגרסה הקודמת.</p></div>
 </section>
 <section class="screen" id="contact" aria-label="פרטי קשר">
  <div class="panel"><div class="panel-head"><div><p class="eyebrow">GLOBAL CONTACT</p><h2>ערוצי תקשורת</h2><p>הערכים המופיעים באתר ובדפי השיווק.</p></div></div><div class="fields two">
   <label>טלפון<span>מספר בינלאומי ללא הסימן +</span><input data-path="contact.phone" inputmode="tel" dir="ltr" autocomplete="tel"></label>
   <label>וואטסאפ<span>מספר בינלאומי</span><input data-path="contact.whatsapp" inputmode="tel" dir="ltr"></label>
   <label>כתובת דוא״ל<span>תיבת יצירת קשר</span><input data-path="contact.email" type="email" dir="ltr" autocomplete="email"></label>
   <label>GitHub<span>קישור מלא לפרופיל</span><input data-path="contact.github" type="url" dir="ltr"></label>
  </div></div>
 </section>
 <section class="screen" id="terminal" aria-label="פרופיל וטרמינל">
  <div class="panel"><div class="panel-head"><div><p class="eyebrow">PROFILE</p><h2>הפרופיל הציבורי</h2><p>עריכת טקסטים שמופיעים במסוף הפרופיל.</p></div></div><div class="fields two"><label>שם משתמש<input data-path="terminal.user" dir="ltr"></label><div></div><label>ביוגרפיה<span>ניתן לכלול HTML התואם לאתר</span><textarea data-path="terminal.bio" rows="5" dir="ltr"></textarea></label><label>כישורים<span>תוכן המסוף, כולל שורות ותגיות</span><textarea data-path="terminal.skills" rows="5" dir="ltr"></textarea></label></div></div>
  <div class="panel"><div class="panel-head"><div><p class="eyebrow">COMMAND LIBRARY</p><h2>פקודות הטרמינל</h2><p>הגדר טריגר, פלט, כפתור יעד וקישור.</p></div><button type="button" id="addCommand" class="btn secondary">+ פקודה חדשה</button></div><div id="commandsList" class="stack"></div></div>
 </section>
 <section class="screen" id="marketing" aria-label="Marketing OS">
  <div class="panel"><div class="panel-head"><div><p class="eyebrow">MULTILINGUAL CONTENT</p><h2>תוכן שיווקי</h2><p>בחר שפה וערוך תוכן ללא שינוי בשפות האחרות.</p></div><div class="lang-switch" role="group" aria-label="בחירת שפה"><button type="button" data-lang="he" class="active">עברית</button><button type="button" data-lang="en">English</button><button type="button" data-lang="es">Español</button></div></div>
  <div class="fields two"><label>כותרת ראשית<input id="marketingTitle"></label><label>תגית מקצועית<input id="marketingBadge"></label><label class="span-2">ביוגרפיה שיווקית<span>תוכן מלא לאזור הראשי</span><textarea id="marketingBio" rows="5"></textarea></label></div>
  </div>
  <div class="panel"><div class="panel-head"><div><p class="eyebrow">SERVICES</p><h2>שירותים ויכולות</h2><p>ניתן לערוך, לשנות סדר ולהוסיף שירותים.</p></div><button type="button" id="addService" class="btn secondary">+ שירות חדש</button></div><div class="stack" id="servicesList"></div></div>
 </section>
 <section class="screen" id="operations" aria-label="מערכת וגרסאות">
  <div class="panel"><div class="panel-head"><div><p class="eyebrow">VERSION CONTROL</p><h2>שמירה ובקרת שינויים</h2></div></div>
   <div class="details-grid"><div><span>גרסת נתונים</span><code id="fullRevision">—</code></div><div><span>שינויים ממתינים</span><strong id="pendingState">—</strong></div><div><span>פעולה אחרונה</span><strong id="lastAction">טרם נשמרו שינויים במפגש</strong></div></div>
   <div class="row-actions"><button type="button" class="btn secondary" id="reloadData">רענן נתונים מהשרת</button><button type="button" class="btn ghost" id="discardChanges">בטל שינויים מקומיים</button></div>
   <p class="footnote">הגיבויים נוצרים בשרת בזמן שמירה מוצלחת; אין כרגע ממשק לניהול גיבויים או שחזור אוטומטי. אין כאן טענה לבדיקת מצב שרת בזמן אמת.</p>
  </div>
 </section>
</main>
</div>
</div>
<div class="bottom-bar" id="bottomBar"><span id="saveStatus" class="save-state"><span class="status-dot"></span> טוען נתונים…</span><div class="bottom-actions"><button type="button" class="btn ghost" id="cancelChanges">בטל שינויים</button><button type="button" class="btn primary" id="saveButton" disabled>שמור באתר <span aria-hidden="true">↗</span></button></div></div>
</body>
</html>