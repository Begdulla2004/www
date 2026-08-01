# JazbaAI: Zamonaviy ta'lim platformasi

## Loyiha haqida

JazbaAI - bu o'qituvchilar va o'quvchilar uchun mo'ljallangan zamonaviy ta'lim platformasi bo'lib, u sun'iy intellekt texnologiyalaridan foydalangan holda ta'lim jarayonini samarali va qiziqarli qilishga mo'ljallangan. Ushbu platforma o'qituvchilarga test va yozma ishlarni yaratish, o'tkazish va baholashda yordam beradi, shuningdek o'quvchilarga interaktiv tarzda bilimlarini sinash imkoniyatini beradi.

## Asosiy imkoniyatlar

### O'qituvchilar uchun:

1. **Guruhlar yaratish**: O'qituvchilar o'z fanlariga oid guruhlar yaratishlari mumkin.
2. **Test va yozma ishlar yaratish**: Platforma ikki turdagi topshiriqlarni qo'llab-quvvatlaydi:
   - **Test savollari (MCQ)**: To'rt variantli test savollari
   - **Yozma ishlar (Essay)**: Ochiq javobli savollar
3. **Jonli sessiyalar o'tkazish**: Kahoot uslubidagi jonli sessiyalar orqali o'quvchilar bilan real vaqtda ishlash
4. **AI yordamida baholash**: Ochiq javobli savollarni sun'iy intellekt yordamida avtomatik baholash
5. **Natijalarni kuzatish**: O'quvchilarning natijalarini ko'rish va tahlil qilish

### O'quvchilar uchun:

1. **Oson kirish**: Maxsus kod orqali guruhlarga qo'shilish
2. **Interaktiv testlar**: Jonli sessiyalarda qatnashish va real vaqtda savollarga javob berish
3. **Tezkor natijalar**: Test savollariga darhol natija olish
4. **Yozma ishlar topshirish**: Ochiq javobli savollarga batafsil javob yozish

## Texnologik yechimlar

JazbaAI quyidagi texnologiyalar asosida qurilgan:

1. **Backend**: PHP dasturlash tili
2. **Ma'lumotlar saqlash**: JSON formatidagi fayllar
3. **Frontend**: HTML, CSS, JavaScript
4. **Sun'iy intellekt**: Gemini API orqali ochiq javoblarni baholash

## Loyihaning arxitekturasi

### Ma'lumotlar modeli:

Barcha ma'lumotlar JSON formatida saqlanadi:
- **users.json**: O'qituvchilar ma'lumotlari
- **groups.json**: Guruhlar ma'lumotlari
- **items.json**: Test va yozma ishlar ma'lumotlari
- **attempts.json**: O'quvchilarning urinishlari

### Asosiy komponentlar:

1. **Autentifikatsiya tizimi**: O'qituvchilar uchun login/parol orqali kirish
2. **Guruhlar boshqaruvi**: Guruhlar yaratish, tahrirlash va boshqarish
3. **Jonli sessiyalar**: Real vaqtda o'quvchilar bilan ishlash
4. **AI baholash tizimi**: Ochiq javoblarni sun'iy intellekt yordamida baholash

## Ishlash jarayoni

1. O'qituvchi tizimga kirib, yangi guruh yaratadi
2. Guruhga test yoki yozma ish qo'shadi
3. Guruhni aktivlashtiradi va maxsus join-kod olinadi
4. O'quvchilar join-kod orqali guruhga qo'shiladi
5. O'qituvchi jonli sessiyani boshlaydi
6. O'quvchilar savollarga javob berishadi
7. Test savollari avtomatik baholanadi
8. Ochiq javoblar AI yordamida baholanadi
9. O'qituvchi natijalarni ko'radi va tahlil qiladi

## Loyihaning afzalliklari

1. **Vaqtni tejash**: O'qituvchilar uchun tekshirish va baholash jarayonini avtomatlashtirish
2. **Obyektivlik**: AI yordamida ochiq javoblarni xolis baholash
3. **Interaktivlik**: O'quvchilar uchun qiziqarli va jalb qiluvchi ta'lim muhiti
4. **Moslashuvchanlik**: Turli fan va mavzular uchun moslashuvchan platforma
5. **Qulay interfeys**: Foydalanuvchilar uchun sodda va tushunarli interfeys

## Kelajakdagi rejalar

1. **Mobile ilovalar**: Android va iOS uchun mobil ilovalar yaratish
2. **Statistika va analitika**: Chuqurlashtirilgan tahlil va hisobotlar
3. **Qo'shimcha savol turlari**: Yangi turdagi savollar qo'shish
4. **Integratsiyalar**: Boshqa ta'lim platformalari bilan integratsiya
5. **Ko'p tillilik**: Turli tillarda ishlash imkoniyati

## Xulosa

JazbaAI - bu zamonaviy ta'lim ehtiyojlariga javob beradigan innovatsion platforma bo'lib, u o'qituvchilar va o'quvchilar o'rtasidagi ta'lim jarayonini samarali, qiziqarli va natijali qilishga yordam beradi. Sun'iy intellekt texnologiyalaridan foydalanish orqali platforma o'qituvchilarga ko'proq vaqt tejash va o'quvchilarga tezkor va xolis baholash imkoniyatini beradi.

JazbaAI - ta'limni yangi bosqichga olib chiqish uchun yaratilgan zamonaviy yechim.