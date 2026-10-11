<?php http_response_code(404); exit; ?>
{
 "id": "sorovnoma",
 "nomi": "Tanishuv so‘rovnomasi",
 "anonim": false,
 "vaqt_daqiqa": 15,
 "korsatma": "Bu test emas — to‘g‘ri yoki noto‘g‘ri javob yo‘q. Ustoz sizni yaxshiroq tanishi va darslarni sizga qiziqarli qilishi uchun samimiy javob bering. Javoblaringizni faqat ustoz ko‘radi.",
 "bolimlar": [
  {
   "nomi": "1. Men haqimda",
   "savollar": [
    {
     "id": "laqab",
     "tur": "text",
     "matn": "Sizni qanday chaqirishimni xohlaysiz? (masalan: Zilola yoki Zila)"
    },
    {
     "id": "tugilgan_kun",
     "tur": "text",
     "matn": "Tug‘ilgan kuningiz (faqat kun va oy, masalan: 15-mart)"
    },
    {
     "id": "sevimli_fan",
     "tur": "multi",
     "max": 3,
     "matn": "Eng yoqtirgan fanlaringiz (ko‘pi bilan 3 ta)",
     "variantlar": [
      "Matematika",
      "Informatika",
      "Ona tili va adabiyot",
      "Ingliz tili",
      "Rus tili",
      "Tarix",
      "Biologiya",
      "Fizika",
      "Geografiya",
      "Jismoniy tarbiya",
      "Tasviriy san’at / Musiqa",
      "Texnologiya"
     ]
    },
    {
     "id": "qiziqish",
     "tur": "multi",
     "matn": "Bo‘sh vaqtingizda nima bilan shug‘ullanasiz?",
     "boshqa": true,
     "variantlar": [
      "Sport",
      "Kitob o‘qish",
      "Rasm chizish",
      "Musiqa / qo‘shiq",
      "Kompyuter o‘yinlari",
      "Ijtimoiy tarmoqlar (Telegram, Instagram, TikTok)",
      "Video montaj / dizayn",
      "Dasturlash / robototexnika",
      "Uy ishlariga yordam berish"
     ]
    },
    {
     "id": "togarak",
     "tur": "text",
     "matn": "Qaysi to‘garak yoki kurslarga qatnashasiz? (bo‘lmasa «yo‘q» deb yozing)"
    },
    {
     "id": "orzu_kasb",
     "tur": "text",
     "matn": "Kelajakda kim bo‘lishni xohlaysiz?"
    }
   ]
  },
  {
   "nomi": "2. Kompyuter va internet",
   "savollar": [
    {
     "id": "uyda_kompyuter",
     "tur": "single",
     "matn": "Uyingizda kompyuter yoki noutbuk bormi?",
     "variantlar": [
      "Ha, o‘zim tez-tez ishlataman",
      "Ha, lekin kam ishlataman",
      "Yo‘q"
     ]
    },
    {
     "id": "telefon",
     "tur": "single",
     "matn": "Smartfoningiz bormi?",
     "variantlar": [
      "Ha, o‘zimniki",
      "Oiladagilarnikidan foydalanaman",
      "Yo‘q"
     ]
    },
    {
     "id": "internet",
     "tur": "single",
     "matn": "Uyda internet bormi?",
     "variantlar": [
      "Ha, doimiy (Wi-Fi)",
      "Faqat telefonda mobil internet",
      "Yo‘q"
     ]
    },
    {
     "id": "ekran_vaqt",
     "tur": "single",
     "matn": "Kuniga telefon yoki kompyuterda o‘rtacha qancha vaqt o‘tkazasiz?",
     "variantlar": [
      "1 soatdan kam",
      "1–2 soat",
      "2–4 soat",
      "4 soatdan ko‘p"
     ]
    },
    {
     "id": "nima_qiladi",
     "tur": "multi",
     "matn": "Telefon yoki kompyuterda ko‘proq nima qilasiz?",
     "boshqa": true,
     "variantlar": [
      "O‘yin o‘ynayman",
      "YouTube / video ko‘raman",
      "Telegram / Instagram / TikTok",
      "Darslarga tayyorlanaman",
      "Kitob o‘qiyman",
      "Video yoki rasm tahrirlayman",
      "Dasturlash o‘rganaman"
     ]
    },
    {
     "id": "dasturlar",
     "tur": "multi",
     "matn": "Qaysi dasturlarda ishlab ko‘rgansiz?",
     "variantlar": [
      "Microsoft Word",
      "PowerPoint",
      "Excel",
      "Paint",
      "Scratch",
      "Python",
      "Canva",
      "CapCut",
      "Minecraft",
      "Hech qaysi"
     ]
    },
    {
     "id": "klaviatura",
     "tur": "single",
     "matn": "Klaviaturada qanday yozasiz?",
     "variantlar": [
      "Tez, deyarli qaramasdan yozaman",
      "O‘rtacha, harflarni qidirib yozaman",
      "Sekin, juda qiynalaman"
     ]
    }
   ]
  },
  {
   "nomi": "3. Informatika fani va men",
   "savollar": [
    {
     "id": "yoqadimi",
     "tur": "scale",
     "min": 1,
     "max": 5,
     "matn": "Informatika fani sizga qanchalik yoqadi?",
     "belgilar": [
      "Umuman yoqmaydi",
      "Juda yoqadi"
     ]
    },
    {
     "id": "qiyinmi",
     "tur": "scale",
     "min": 1,
     "max": 5,
     "matn": "Informatika siz uchun qanchalik qiyin?",
     "belgilar": [
      "Juda oson",
      "Juda qiyin"
     ]
    },
    {
     "id": "oldingi_yillar",
     "tur": "single",
     "matn": "5–6-sinflarda informatika darsida kompyuterda qanchalik ko‘p ishlagansiz?",
     "variantlar": [
      "Deyarli har darsda",
      "Ba’zan",
      "Juda kam",
      "Umuman ishlamaganmiz"
     ]
    },
    {
     "id": "esda_qolgan",
     "tur": "textarea",
     "matn": "5–6-sinf informatika darslaridan nimalar esingizda qolgan?"
    },
    {
     "id": "qiziq_mavzu",
     "tur": "multi",
     "matn": "Informatikada nimani o‘rganish sizga eng qiziq?",
     "variantlar": [
      "Kompyuter qanday ishlashi",
      "Dasturlash (o‘yin yaratish)",
      "Robototexnika",
      "Sayt yaratish",
      "Dizayn, rasm, video",
      "Internet xavfsizligi",
      "Hujjat va taqdimotlar (Word, PowerPoint)",
      "Sun’iy intellekt"
     ]
    },
    {
     "id": "qiyin_narsa",
     "tur": "textarea",
     "matn": "Informatika darsida sizga nima qiyin yoki tushunarsiz?"
    }
   ]
  },
  {
   "nomi": "4. Qanday o‘rganishni yoqtiraman",
   "savollar": [
    {
     "id": "tushunish_usuli",
     "tur": "single",
     "matn": "Yangi mavzuni qachon yaxshiroq tushunasiz?",
     "variantlar": [
      "Ustoz ko‘rsatib bersa (ko‘rib)",
      "Tinglab, eshitib",
      "O‘zim qilib ko‘rsam (amaliyot)",
      "O‘qib, yozib"
     ]
    },
    {
     "id": "ish_shakli",
     "tur": "single",
     "matn": "Darsda qanday ishlash sizga yoqadi?",
     "variantlar": [
      "Yolg‘iz o‘zim",
      "Juftlikda",
      "Kichik guruhda",
      "Butun sinf bilan birga"
     ]
    },
    {
     "id": "savol_berish",
     "tur": "single",
     "matn": "Tushunmasangiz, darsda savol berasizmi?",
     "variantlar": [
      "Ha, bemalol so‘rayman",
      "Ba’zan, uyalaman",
      "Yo‘q, so‘ramayman"
     ]
    },
    {
     "id": "korish",
     "tur": "single",
     "matn": "O‘rningizdan doska yoki ekranni yaxshi ko‘ra olasizmi?",
     "variantlar": [
      "Ha, yaxshi ko‘raman",
      "Ba’zan qiynalaman",
      "Oldinroqda o‘tirishim kerak"
     ]
    }
   ]
  },
  {
   "nomi": "5. O‘zimni baholayman",
   "savollar": [
    {
     "id": "oz_baho",
     "tur": "matrix",
     "matn": "Har bir gapga o‘zingizga mos javobni belgilang.",
     "qatorlar": [
      "Papka yaratib, faylni kerakli joyga saqlay olaman",
      "Word’da matn yozib, uni bezay olaman (shrift, rang, tekislash)",
      "Klaviaturada tez va xatosiz yoza olaman",
      "Scratch’da oddiy dastur (animatsiya) tuza olaman",
      "Algoritm nima ekanini tushuntira olaman",
      "Bit va bayt nima ekanini bilaman",
      "Sonni ikkilik sanoq sistemasiga o‘tkaza olaman",
      "Internetdan kerakli ma’lumotni tez topa olaman",
      "Internetda o‘zimni firibgarlardan himoya qila olaman",
      "Kompyuterda qiyinchilik chiqsa, o‘zim yechim topa olaman"
     ],
     "ustunlar": [
      "Qila olmayman",
      "Qiynalaman",
      "Yordam bilan qila olaman",
      "Mustaqil qila olaman"
     ]
    }
   ]
  },
  {
   "nomi": "6. Ustozga xabar",
   "savollar": [
    {
     "id": "ustoz_bilsin",
     "tur": "textarea",
     "matn": "Ustoz, men haqimda bilishingiz kerak bo‘lgan narsa (ixtiyoriy):"
    },
    {
     "id": "dars_qanday",
     "tur": "textarea",
     "matn": "Informatika darslari qanday bo‘lsa, sizga qiziqroq bo‘lardi?"
    }
   ]
  }
 ]
}