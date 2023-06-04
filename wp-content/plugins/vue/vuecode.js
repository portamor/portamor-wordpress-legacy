/* const { createApp } = Vue

  createApp({
    data() {
      return {
        message: 'Hello Vue! wiiii',
        specialists: [
          {
            id: 1,
            name: 'Juan Perez',
            proffession: 'Psicologo',
            number: '999999999',
            img:  'https://i.pravatar.cc/300'
          },
          {
            id: 2,
            name: 'Maria Carde',
            proffession: 'Psicologa',
            number: '999999999',
            img: 'https://i.pravatar.cc/300'
          },
          {
            id: 3,
            name: 'Medali',
            proffession: 'Enfermera',
            number: '999999999',
            img: 'https://i.pravatar.cc/300'
          },
        ]
      }
    }
  }).mount('#app') */

  const { createApp } = Vue

  const specialistlist = [
  {
    id: 1,
    name: 'Malena Odar Olivera',
    proffession: 'Abogada',
    number: '999999999',
    img:  'https://www.assyst.de/cms/upload/sub/digitalisierung/18-F.jpg',
    description: 'Mi nombre es Malena Jacqueline Odar Olivera, tengo 28 años, Abogada con estudios más específicos en Derecho Laboral, Procesal Laboral, Derecho Administrativo y Recursos Humanos. Me considero una persona de personalidad proactiva, responsable, competitiva y eficaz en la toma de decisiones. Me gusta viajar, leer, bailar y disfrutar de los momentos de la vida donde soy feliz.',
    category: 1
  },
  {
    id: 2,
    name: 'Javier Petit Azuaje',
    proffession: 'Psicologa',
    number: '999999999',
    img: 'https://www.assyst.de/cms/upload/sub/digitalisierung/15-M.jpg',
    description: 'Mi nombre es Javier Petit y soy Licenciado en Educación Física con una Especialidad en Rehabilitación Deportiva, además de ser Instructor de Pausas Activas (Gimnasia Laboral) y coach de Bienestar. Conozco las necesidades de mis clientes y las dificultades que presentan para el logro de sus objetivos a nivel de salud y bienestar.',
    category: 1
  },
  {
    id: 3,
    name: 'Angie Arakaki Oyata',
    proffession: 'Psicologa',
    number: '999999999',
    img: 'https://www.assyst.de/cms/upload/sub/digitalisierung/15-M.jpg',
    description: 'Hola soy Angie. Profesional en Psicología con 20 años de experiencia profesional, .Soy una persona alegre, constante con mucha motivación en lo que haga. Trato de dar lo mejor cada día, de transmitir y entregar cosas nuevas para los demás y para mi. Una persona empática y lider',
    category: 2
  },
  {
    id: 3,
    name: 'Fernanda Serrano',
    proffession: 'Instructora',
    number: '999999999',
    img: 'https://media.licdn.com/dms/image/C4E12AQEVO-ZAozxJ3Q/article-cover_image-shrink_600_2000/0/1534479718033?e=2147483647&v=beta&t=ehPjnCD2Nz7aFYN13mZVQ0hww3LG_5pFo_YGjh_6tMk',
    description: 'Hola! Soy Fernanda Serrano y amo bailar! Soy instructora deportiva desde hace 15 años y comencé a bailar hace unos 10 años. Me encanta compartir mi energía con ustedes y recibir la suya cuando bailamos. Soy una persona positiva y me gusta disfrutar de los detalles que la vida nos regala y también aprender de los desafíos que se nos presentan. Hace 6 meses me lesioné la rodilla y estuve imposibilitada de bailar por varios meses. Y fue el momento perfecto para poner en práctica esta modalidad de baile en silla que me alegró y me hizo sentir que no habían límites para hacer lo que amamos. Por eso lo comparto hoy con la comunidad de PORTAMOR. Gracias por participar de esta clase modelo y ojalá la puedan repetir y disfrutar conforme aprenden un poco más los pasos.',
    category: 3
  },
  {
    id: 3,
    name: 'Lilia Herrera Williams',
    proffession: 'Maestra',
    number: '999999999',
    img: 'https://assets-global.website-files.com/61dc0796f359b6145bc06ea6/633d83c8cbd0cce86ce8cbe6_TransparentAvatar-WebsiteHero-thumb.png',
    description: 'Mi nombre es Lilia Herrera Williams, nací en Arequipa Soy Maestra de profesión y por vocación Apasionada por la Lectura y Escritura Me encantan las Artes Manuales, amo pintar, coser y tejer Un día sin crear algo es para mí tiempo perdido Soy madre de 5 hijos y abuela de 13 nietos, entre ellos hay músicos, pintores, escritores y  danzantes Me apasiona el cine',
    category: 2
  },
  {
    id: 3,
    name: 'Guadalupe Nuñez Rojas',
    proffession: 'Nutricionista',
    number: '999999999',
    img: 'https://www.assyst.de/cms/upload/sub/digitalisierung/18-F.jpg',
    description: 'Nutricionista con Diplomado en Nutrición Clínica. Actualmente investigadora junior en proyecto internacional',
    category: 4
  },
]

  createApp({
    data() {
      return {
        message: 'Hello Vue! wiiii',
        specialists: specialistlist
      }
    },
    methods: {
      filterby(id) {
        this.specialists = specialistlist.filter((specialist) => specialist.category == id);
      },
      contactSpecialist(number) {
        window.open(`https://api.whatsapp.com/send?phone=${number}`, `_blank`);
      },
      limitStringLength(str, maxLength) {
        return str.length <= maxLength ? str : str.substring(0, maxLength) + "...";
      }
    }
  }).mount('#app')