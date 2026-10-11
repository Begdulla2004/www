<?php http_response_code(404); exit; ?>
{
 "id": "fikr",
 "nomi": "Informatika darslari haqida fikringiz (anonim)",
 "anonim": true,
 "qachon": "3 oydan keyin, yakuniy test bilan bir haftada",
 "vaqt_daqiqa": 10,
 "korsatma": "Bu so‘rovnoma ANONIM — ismingiz yozilmaydi va ustoz kim nima yozganini bilmaydi. Halol javob bering: bu ustozga darslarni yaxshilashga yordam beradi.",
 "bolimlar": [
  {
   "nomi": "1. Fanga munosabat (oktabrdagi so‘rovnoma bilan solishtiriladi)",
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
    }
   ]
  },
  {
   "nomi": "2. Darslar haqida",
   "savollar": [
    {
     "id": "tushunarli",
     "tur": "scale",
     "min": 1,
     "max": 5,
     "matn": "Ustoz mavzularni qanchalik tushunarli tushuntiradi?",
     "belgilar": [
      "Umuman tushunarsiz",
      "Juda tushunarli"
     ]
    },
    {
     "id": "qiziqarli",
     "tur": "scale",
     "min": 1,
     "max": 5,
     "matn": "Darslar qanchalik qiziqarli?",
     "belgilar": [
      "Juda zerikarli",
      "Juda qiziqarli"
     ]
    },
    {
     "id": "temp",
     "tur": "single",
     "matn": "Darslar tezligi (tempi) sizga qanday?",
     "variantlar": [
      "Juda tez — ulgurmayman",
      "Me’yorida",
      "Sekin — zerikaman"
     ]
    },
    {
     "id": "amaliyot",
     "tur": "single",
     "matn": "Darsda kompyuterda amaliy ishlash yetarlimi?",
     "variantlar": [
      "Ko‘p, yetarli",
      "O‘rtacha",
      "Kam, ko‘proq bo‘lsin"
     ]
    },
    {
     "id": "savol_erkin",
     "tur": "scale",
     "min": 1,
     "max": 5,
     "matn": "Tushunmasangiz, ustozdan bemalol so‘ray olasizmi?",
     "belgilar": [
      "Umuman yo‘q",
      "Har doim bemalol"
     ]
    },
    {
     "id": "adolatli",
     "tur": "scale",
     "min": 1,
     "max": 5,
     "matn": "Ustoz baholarni qanchalik adolatli qo‘yadi?",
     "belgilar": [
      "Adolatsiz",
      "Juda adolatli"
     ]
    },
    {
     "id": "eng_yaxshi_dars",
     "tur": "textarea",
     "matn": "Shu 3 oyda qaysi dars yoki mashg‘ulot sizga eng yoqdi? Nima uchun?"
    },
    {
     "id": "ozgartirish",
     "tur": "textarea",
     "matn": "Darslarda nimani o‘zgartirish kerak?"
    },
    {
     "id": "saqlash",
     "tur": "textarea",
     "matn": "Darslarda nimani albatta saqlab qolish kerak?"
    }
   ]
  },
  {
   "nomi": "3. O‘zimni baholayman (oktabrdagi bilan bir xil)",
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
  }
 ]
}