# MediQuick Pharmacy — ස්ථාපනය හා ධාවන මාර්ගෝපදේශය

## 1. හැඳින්වීම

**MediQuick Pharmacy** යනු කුරුණෑගල නගරයේ පිහිටි (ප්‍රාථමික) නව්‍ය ඔන්ලයින් ෆාමසියක් සඳහා
නිර්මාණය කරන ලද වෙබ් යෙදුමකි. මෙය CSE4206 (Web Application Development) විෂය මාලාවේ
පැවරුම සඳහා සකසන ලද සම්පූර්ණ ක්‍රියාකාරී වෙබ් අඩවියකි.

මෙම පද්ධතිය හරහා පාරිභෝගිකයන්ට බෙහෙත් වට්ටෝරු උඩුගත කර, බෙහෙත් හා සෞඛ්‍ය නිෂ්පාදන
මිලදී ගෙන, ඒවා නිවසටම ගෙන්වා ගත හැක. ෆාමසි කාර්ය මණ්ඩලයට වට්ටෝරු පරීක්ෂා කර අනුමත කිරීම,
ඇණවුම් කළමනාකරණය කිරීම සහ තොග (stock) නිරීක්ෂණය කිරීම කළ හැක. පරිපාලකවරයාට සමස්ත
පද්ධතිය අධීක්ෂණය කිරීම, කාර්ය මණ්ඩල ගිණුම් කළමනාකරණය කිරීම සහ වාර්තා (reports) නැරඹීම
කළ හැක.

### පද්ධතියේ පරිශීලක වර්ග තුනක් (User Roles)

| වර්ගය | කළ හැකි දේ |
|---|---|
| **පාරිභෝගිකයා (Customer)** | ගිණුමක් සාදා, බෙහෙත් සොයා, කරත්තයට (cart) එකතු කර, වට්ටෝරු උඩුගත කර, ඇණවුම් කර, ඒවායේ ප්‍රගතිය නිරීක්ෂණය කිරීම |
| **කාර්ය මණ්ඩලය (Staff)** | වට්ටෝරු පරීක්ෂා කර අනුමත/ප්‍රතික්ෂේප කිරීම, ඇණවුම් තත්ත්වය යාවත්කාලීන කිරීම, නිෂ්පාදන හා තොග කළමනාකරණය, පාරිභෝගික විමසුම්වලට පිළිතුරු දීම |
| **පරිපාලක (Admin)** | කාර්ය මණ්ඩල ගිණුම් සෑදීම/අත්හිටුවීම, විකුණුම් වාර්තා හා ප්‍රස්ථාරය (chart) නැරඹීම, ප්‍රවර්ග (categories) කළමනාකරණය, පද්ධතියේ ක්‍රියාකාරකම් (audit log) නිරීක්ෂණය |

### භාවිතා කර ඇති තාක්ෂණයන් (Technology)

- **Front-end:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Back-end:** PHP 8 (framework කිසිවක් නොමැතිව, සරල PHP)
- **දත්ත ගබඩාව (Database):** MySQL (වගු 12ක්)
- **Local Server:** XAMPP (Apache + PHP + MySQL එකට ලබා දෙන පැකේජය)
- **ගෙවීම් (Payment):** PayHere Sandbox (කාඩ්පත) සහ Cash on Delivery
- **ප්‍රස්ථාර (Charts):** Chart.js

---

## 2. අවශ්‍ය දේ (Requirements)

ධාවනය කිරීමට පෙර පහත දේ ඔබගේ පරිගණකයේ තිබිය යුතුය:

1. **Windows** පරිගණකයක්
2. **XAMPP** මෘදුකාංගය — නොමිලේ බාගත කළ හැක: https://www.apachefriends.org/download.html
3. අන්තර්ජාල සම්බන්ධතාවයක් (Bootstrap, Font Awesome, Google Fonts වැනි CDN සම්පත් load
   වීමට)

---

## 3. ස්ථාපනය කරන ආකාරය (Installation)

### පියවර 1 — XAMPP ස්ථාපනය කිරීම

ඉහත සබැඳියෙන් XAMPP බාගත කර, සාමාන්‍ය (default) සැකසුම් සමඟින් ස්ථාපනය කරන්න. එය
සාමාන්‍යයෙන් `C:\xampp` වෙත ස්ථාපනය වේ.

### පියවර 2 — ව්‍යාපෘති ෆෝල්ඩරය පිටපත් කිරීම

මෙම ව්‍යාපෘතියේ `mediquick` නම් ෆෝල්ඩරය, XAMPP හි `htdocs` ෆෝල්ඩරය තුළට පිටපත් කරන්න:

```
D:\Cuti Aiya\project 1\mediquick  →  C:\xampp\htdocs\mediquick
```

PowerShell හරහා මෙසේ කළ හැක:

```powershell
Copy-Item "D:\Cuti Aiya\project 1\mediquick" -Destination "C:\xampp\htdocs\mediquick" -Recurse
```

### පියවර 3 — Apache සහ MySQL ආරම්භ කිරීම

**XAMPP Control Panel** විවෘත කර, **Apache** සහ **MySQL** දෙකෙහිම **Start** බොත්තම ඔබන්න.
දෙකම කොළ පාටින් (running) පෙන්විය යුතුය.

### පියවර 4 — දත්ත ගබඩාව import කිරීම

1. Browser එකක `http://localhost/phpmyadmin` විවෘත කරන්න.
2. ඉහළින් ඇති **Import** tab එක ක්ලික් කරන්න.
3. **Choose File** ඔබා, `database\schema.sql` ගොනුව තෝරාගෙන **Go** ඔබන්න.
   (මෙය `mediquick_db` නමින් දත්ත ගබඩාවක් සහ වගු 12ක් සාදයි.)
4. නැවත **Import** ගොස්, මෙවර `database\seed.sql` ගොනුව තෝරාගෙන **Go** ඔබන්න.
   (මෙය නියැදි නිෂ්පාදන, ලිපි, සහ පරීක්ෂණ ගිණුම් 3ක් එකතු කරයි.)

---

## 4. පද්ධතිය ධාවනය කිරීම (How to run)

ඉහත සියලු පියවර් සම්පූර්ණ කළ පසු, browser එකක පහත ලිපිනය ටයිප් කරන්න:

```
http://localhost/mediquick/
```

මෙයින් MediQuick Pharmacy හි home page එක විවෘත විය යුතුය.

### පරීක්ෂණ ගිණුම් (Test accounts)

`seed.sql` හරහා සාදන ලද ගිණුම් තුනකින් පද්ධතියේ සියලුම කොටස් පරීක්ෂා කළ හැක. සියලුම
ගිණුම්වල මුරපදය: **Password123**

| භූමිකාව | ඊමේල් ලිපිනය | මුරපදය |
|---|---|---|
| පාරිභෝගිකයා | customer@example.com | Password123 |
| කාර්ය මණ්ඩලය | staff@mediquick.lk | Password123 |
| පරිපාලක | admin@mediquick.lk | Password123 |

**Login** පිටුවෙන් (`http://localhost/mediquick/login.php`) මෙම ගිණුම්වලට පිවිසෙන්න.
භූමිකාවට අනුව ඔබව ස්වයංක්‍රීයව නිවැරදි කොටසට යොමු කරයි —
පාරිභෝගිකයන් My Account එකටත්, කාර්ය මණ්ඩලය Staff Panel එකටත්, පරිපාලකයන් Admin Panel
එකටත්.

---

## 5. පියවරෙන් පියවර පරීක්ෂා කරන ආකාරය (Sample walkthrough)

1. `customer@example.com` ලෙස පිවිසෙන්න.
2. **Shop** පිටුවට ගොස් නිෂ්පාදනයක් තෝරන්න (උදා: Amoxicillin — Rx ලේබලය සහිත බෙහෙතක්).
3. එය කරත්තයට (cart) එකතු කර, **Checkout** වෙත යන්න.
4. Rx (වට්ටෝරු අවශ්‍ය) නිෂ්පාදනයක් තිබේ නම්, පළමුව **Upload prescription** පිටුවෙන් ඡායාරූප/PDF
   ගොනුවක් උඩුගත කරන්න, පසුව checkout පිටුවට ආපසු පැමිණ එය තෝරන්න.
5. ගෙවීම් ක්‍රමය ලෙස **Cash on Delivery** තෝරා ඇණවුම තහවුරු කරන්න.
6. දැන් **log out** වී, `staff@mediquick.lk` ලෙස පිවිසෙන්න.
7. **Prescription queue** වෙත ගොස්, උඩුගත කළ වට්ටෝරුව පරීක්ෂා කර **Approve** කරන්න —
   ඇණවුම ස්වයංක්‍රීයව ඊළඟ අදියරට ගමන් කරයි.
8. **Orders** පිටුවෙන් එම ඇණවුම Packed → Dispatched → Delivered ලෙස යාවත්කාලීන කරන්න.
9. `admin@mediquick.lk` ලෙස පිවිසී, **Dashboard** එකෙන් විකුණුම් ප්‍රස්ථාරය (revenue chart)
   සහ **Audit log** එකෙන් සිදු වූ සියලුම ක්‍රියාකාරකම් නරඹන්න.

---

## 6. ගැටළු නිරාකරණය (Troubleshooting)

| ගැටළුව | විසඳුම |
|---|---|
| "Database connection failed" පණිවිඩය පෙන්වයි | XAMPP Control Panel එකේ **MySQL** service එක Start කර ඇත්දැයි පරීක්ෂා කරන්න. |
| පිටුව සම්පූර්ණයෙන් හිස්ව පෙන්වයි | **Apache** service එක Start කර ඇත්දැයි පරීක්ෂා කරන්න. Port 80 වෙනත් program එකක් (Skype, IIS වැනි) විසින් භාවිතා කරමින් සිටී නම් XAMPP config හි port වෙනස් කරන්න. |
| Login කළ නොහැක | `database/seed.sql` import කර ඇත්දැයි තහවුරු කරන්න — එමගින් පමණක් පරීක්ෂණ ගිණුම් සෑදේ. |
| CSS/පින්තූර පෙන්නන්නේ නැත | අන්තර්ජාල සම්බන්ධතාවය පරීක්ෂා කරන්න — Bootstrap, Font Awesome, Google Fonts CDN හරහා load වේ. |
| වට්ටෝරු ගොනුව upload කළ නොහැක | ගොනුව JPG, PNG හෝ PDF විය යුතුය, ප්‍රමාණය 5MB ට වඩා අඩු විය යුතුය. |

---

## 7. ව්‍යාපෘති ව්‍යුහය (Project structure — කෙටියෙන්)

```
mediquick/
├── includes/     — පොදු PHP කේතය (database, login/logout, ආරක්ෂාව, helper functions)
├── assets/css/   — එක් CSS ගොනුවක් සියලුම කොටස් සඳහා
├── *.php (root)  — home, shop, cart, checkout ආදී පොදු පිටු
├── account/      — පාරිභෝගික කොටස
├── staff/        — කාර්ය මණ්ඩල කොටස
├── admin/        — පරිපාලක කොටස
├── payhere/      — PayHere ගෙවීම් integration
└── uploads/rx/   — උඩුගත කළ වට්ටෝරු ගොනු (කෙළින්ම access කළ නොහැක)
```

වැඩිදුර විස්තර (ඉංග්‍රීසි) සඳහා [mediquick/README.md](../mediquick/README.md) බලන්න, සහ
සම්පූර්ණ PRD/design pack එක සඳහා [mediquick-plan.html](mediquick-plan.html) බලන්න.
