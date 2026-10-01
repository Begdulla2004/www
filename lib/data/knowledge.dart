class DiseaseInfo {
  final String name;
  final String latin;
  final String type;
  final String crops;
  final String symptoms;
  final String treatment;

  const DiseaseInfo({
    required this.name,
    required this.latin,
    required this.type,
    required this.crops,
    required this.symptoms,
    required this.treatment,
  });
}

const diseases = [
  DiseaseInfo(
    name: 'Fitoftoroz',
    latin: 'Phytophthora infestans',
    type: "Zamburug'simon",
    crops: 'Pomidor, kartoshka',
    symptoms: "Barglarda qo'ng'ir-qora, suvli dog'lar paydo bo'ladi; nam havoda barg ostida oq g'ubor hosil bo'ladi. Mevalarda qattiq qo'ng'ir dog'lar.",
    treatment: "Kasal barglarni olib tashlang. Mis saqlovchi preparatlar yoki mankotseb, metalaksil + mankotseb asosidagi fungitsidlar bilan 7–10 kunda ishlov bering. Tomchilatib sug'oring.",
  ),
  DiseaseInfo(
    name: 'Alternarioz',
    latin: 'Alternaria solani',
    type: "Zamburug'li",
    crops: 'Pomidor, kartoshka, baqlajon',
    symptoms: "Pastki barglarda konsentrik halqali (nishonga o'xshash) qo'ng'ir dog'lar, atrofi sarg'ayadi.",
    treatment: "Zararlangan barglarni yo'qoting. Mankotseb, xlorotalonil yoki azoksistrobin asosidagi fungitsidlar. Almashlab ekishga rioya qiling.",
  ),
  DiseaseInfo(
    name: 'Septorioz',
    latin: 'Septoria lycopersici',
    type: "Zamburug'li",
    crops: 'Pomidor',
    symptoms: "Mayda, kulrang markazli, to'q hoshiyali ko'plab dog'lar; pastki barglardan boshlanadi, barglar sarg'ayib to'kiladi.",
    treatment: "Pastki kasal barglarni olib tashlang, bargni ho'llamay sug'oring. Mis saqlovchi preparatlar yoki xlorotalonil bilan ishlov bering.",
  ),
  DiseaseInfo(
    name: 'Un-shudring',
    latin: 'Erysiphales',
    type: "Zamburug'li",
    crops: "Bodring, qovoq, uzum, olma, atirgul",
    symptoms: "Barg ustida oq, unga o'xshash g'ubor; barglar burishib quriydi.",
    treatment: "Oltingugurt preparatlari, tebukonazol yoki triadimefon asosidagi fungitsidlar. Qalin ekishdan saqlaning, shamollatishni yaxshilang.",
  ),
  DiseaseInfo(
    name: 'Soxta un-shudring (peronosporoz)',
    latin: 'Peronosporaceae',
    type: "Zamburug'simon",
    crops: 'Bodring, uzum, piyoz',
    symptoms: "Barg ustida sariq burchakli dog'lar, barg ostida kulrang-binafsha g'ubor.",
    treatment: "Mis saqlovchi preparatlar, metalaksil yoki fosetil-alyuminiy asosidagi fungitsidlar. Ertalab sug'oring, bargni ho'llamang.",
  ),
  DiseaseInfo(
    name: 'Zang kasalligi',
    latin: 'Pucciniales',
    type: "Zamburug'li",
    crops: "Bug'doy, loviya, olma, atirgul",
    symptoms: "Barg ostida zarg'aldoq yoki jigarrang yostiqchalar, qo'l bilan ishqalanganda chang chiqadi.",
    treatment: "Triazol guruhidagi fungitsidlar (tebukonazol, propikonazol). Kasal barglarni yig'ib yo'qoting.",
  ),
  DiseaseInfo(
    name: 'Antraknoz',
    latin: 'Colletotrichum spp.',
    type: "Zamburug'li",
    crops: 'Bodring, qovun, tarvuz, loviya',
    symptoms:
        "Barg va mevalarda botiq, qo'ng'ir-qora dog'lar; nam havoda pushti rangli yostiqchalar.",
    treatment: "Mis saqlovchi preparatlar yoki azoksistrobin. Sog'lom urug' eking, o'simlik qoldiqlarini yo'qoting.",
  ),
  DiseaseInfo(
    name: 'Kulrang chirish',
    latin: 'Botrytis cinerea',
    type: "Zamburug'li",
    crops: 'Uzum, qulupnay, pomidor',
    symptoms: "Mevalar va barglarda kulrang momiqsimon mog'or, to'qimalar yumshab chiriydi.",
    treatment: "Shamollatishni yaxshilang, kasal qismlarni olib tashlang. Fludioksonil, fenheksamid yoki biologik preparatlar (Trixodermin).",
  ),
  DiseaseInfo(
    name: 'Olma qo\'tiri',
    latin: 'Venturia inaequalis',
    type: "Zamburug'li",
    crops: 'Olma, nok',
    symptoms: "Barglarda zaytun-qora, baxmalsimon dog'lar; mevalarda qora yoriq dog'lar.",
    treatment: "Erta bahorda mis preparatlari, keyin difenokonazol yoki ditianon asosidagi fungitsidlar. To'kilgan barglarni yig'ing.",
  ),
  DiseaseInfo(
    name: "Bakterial dog'lanish",
    latin: 'Xanthomonas / Pseudomonas spp.',
    type: 'Bakterial',
    crops: 'Pomidor, qalampir, bodring',
    symptoms: "Mayda, suvli, keyin qorayadigan dog'lar, atrofida sariq hoshiya.",
    treatment: "Mis saqlovchi preparatlar bilan ishlov bering, kasal o'simliklarni olib tashlang. Urug'ni ekishdan oldin zararsizlantiring.",
  ),
  DiseaseInfo(
    name: 'Mozaika virusi',
    latin: 'TMV / CMV',
    type: 'Virusli',
    crops: 'Pomidor, bodring, qalampir',
    symptoms: "Barglarda ola-bula och va to'q yashil mozaika, barglar burishadi va kichrayadi.",
    treatment: "Davosi yo'q. Kasal o'simliklarni yo'qoting, virusni tashuvchi shira bitlarga qarshi kurashing, asboblarni zararsizlantiring.",
  ),
  DiseaseInfo(
    name: "O'rgimchakkana",
    latin: 'Tetranychus urticae',
    type: 'Zararkunanda',
    crops: "Bodring, pomidor, g'o'za, mevali daraxtlar",
    symptoms: "Barglarda mayda oq-sariq nuqtalar, barg ostida ingichka to'r; barglar quriydi.",
    treatment: "Akaritsidlar (abamektin va boshqalar) bilan ishlov bering, preparatlarni almashtirib turing. Havo namligini oshiring.",
  ),
  DiseaseInfo(
    name: 'Shira bit',
    latin: 'Aphidoidea',
    type: 'Zararkunanda',
    crops: "Deyarli barcha ekinlar",
    symptoms:
        "Barglar buraladi, yopishqoq shira qoladi, novdalar uchida mayda hasharotlar to'dasi.",
    treatment: "Sovunli suv yoki biologik preparatlar; kuchli zararlanganda insektitsidlar. Xonqizi kabi foydali hasharotlarni asrang.",
  ),
];

class TipGroup {
  final String title;
  final String iconName;
  final List<String> tips;

  const TipGroup({required this.title, required this.iconName, required this.tips});
}

const tipGroups = [
  TipGroup(
    title: "Sug'orish",
    iconName: 'water',
    tips: [
      "Ertalab yoki kechqurun sug'oring — kunduzgi issiqda suv tez bug'lanadi.",
      "Bargni emas, ildiz atrofini sug'oring: nam barglar zamburug' kasalliklarini ko'paytiradi.",
      "Tuproq 3–5 sm chuqurlikda qurigandagina sug'oring, ortiqcha suv ildizni chiritadi.",
      "Tomchilatib sug'orish suvni tejaydi va kasalliklar xavfini kamaytiradi.",
    ],
  ),
  TipGroup(
    title: "O'g'itlash",
    iconName: 'fertilizer',
    tips: [
      "Azot o'sish davrida, fosfor va kaliy gullash va meva tugish davrida muhim.",
      "Ortiqcha azot barglarni yumshatib, kasallik va zararkunandalarga moyil qiladi.",
      "Organik o'g'itlar (chirigan go'ng, kompost) tuproq tuzilishini yaxshilaydi.",
      "Barglar sarg'aysa — azot yoki temir yetishmasligi bo'lishi mumkin, tahlil qilib ko'ring.",
    ],
  ),
  TipGroup(
    title: "Selitra va asosiy o'g'itlar",
    iconName: 'fertilizer',
    tips: [
      "Ammiakli selitra (N 34%) — o'sish davri uchun azot. Tuproqqa 1 m² ga 15–20 g, sug'orishdan oldin beriladi.",
      "Kaliyli selitra (N 13%, K 46%) — gullash va meva tugish davrida. Barg orqali 10 L suvga 50–100 g.",
      "Kalsiyli selitra (N 15,5%, Ca 19%) — pomidor va qalampirda mevaning uchki chirishiga qarshi. Barg orqali 10 L suvga 20–50 g.",
      "Karbamid (N 46%) — tez ta'sir qiluvchi azot. Barg orqali 10 L suvga 30–50 g, kuchli quyoshda purkamang.",
      "Ammofos va superfosfat — fosfor: ildiz va gullash uchun, asosan ekishdan oldin tuproqqa solinadi.",
      "Zamburug' kasalligi bor o'simlikka ortiqcha azot bermang — barglar yumshab, kasallik kuchayadi.",
      "Aniq me'yor ekin, tuproq va o'sish davriga bog'liq — qadoqdagi yo'riqnomaga amal qiling.",
    ],
  ),
  TipGroup(
    title: 'Parvarish',
    iconName: 'care',
    tips: [
      "O'simliklarni juda zich ekmang — shamollatish kasalliklarni kamaytiradi.",
      "Pastki, tuproqqa tegib turgan barglarni olib tashlang.",
      "Begona o'tlarni muntazam yulib turing, ular zararkunandalar uchun makon.",
      "Mulchalash tuproq namligini saqlaydi va begona o'tlarni kamaytiradi.",
    ],
  ),
  TipGroup(
    title: 'Kasalliklarning oldini olish',
    iconName: 'shield',
    tips: [
      "Har yili ekinlarni almashlab eking — bir joyga bir xil ekinni qayta ekmang.",
      "Kasal o'simlik qoldiqlarini daladan olib chiqib yo'qoting, kompostga solmang.",
      "Asboblarni ishlatgandan keyin zararsizlantiring.",
      "O'simliklarni haftasiga kamida bir marta ko'zdan kechiring — erta aniqlash hosilni saqlaydi.",
    ],
  ),
  TipGroup(
    title: 'Dorilarni xavfsiz ishlatish',
    iconName: 'safety',
    tips: [
      "Har doim yorliqdagi me'yor va ko'rsatmalarga amal qiling.",
      "Qo'lqop, niqob va ko'zoynak taqing; shamolli havoda purkamang.",
      "Hosil yig'ishdan oldingi kutish muddatiga rioya qiling.",
      "Bir xil preparatni ketma-ket ko'p ishlatmang — zararkunandalar chidamli bo'lib qoladi.",
    ],
  ),
];
