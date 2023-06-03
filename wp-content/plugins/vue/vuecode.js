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
    name: 'Juan Perez',
    proffession: 'Psicologo',
    number: '999999999',
    img:  'https://www.assyst.de/cms/upload/sub/digitalisierung/18-F.jpg',
    category: 1
  },
  {
    id: 2,
    name: 'Maria Carde',
    proffession: 'Psicologa',
    number: '999999999',
    img: 'https://www.assyst.de/cms/upload/sub/digitalisierung/15-M.jpg',
    category: 1
  },
  {
    id: 3,
    name: 'Medali',
    proffession: 'Enfermera',
    number: '999999999',
    img: 'https://assets-global.website-files.com/61dc0796f359b6145bc06ea6/633d83c8cbd0cce86ce8cbe6_TransparentAvatar-WebsiteHero-thumb.png',
    category: 2
  },
  {
    id: 3,
    name: 'Marcelo',
    proffession: 'Enfermera',
    number: '999999999',
    img: 'https://media.licdn.com/dms/image/C4E12AQEVO-ZAozxJ3Q/article-cover_image-shrink_600_2000/0/1534479718033?e=2147483647&v=beta&t=ehPjnCD2Nz7aFYN13mZVQ0hww3LG_5pFo_YGjh_6tMk',
    category: 3
  },
  {
    id: 3,
    name: 'José Luis',
    proffession: 'Ingeniero',
    number: '999999999',
    img: 'https://assets-global.website-files.com/61dc0796f359b6145bc06ea6/633d83c8cbd0cce86ce8cbe6_TransparentAvatar-WebsiteHero-thumb.png',
    category: 2
  },
  {
    id: 3,
    name: 'Antoni',
    proffession: 'Medico',
    number: '999999999',
    img: 'https://www.assyst.de/cms/upload/sub/digitalisierung/18-F.jpg',
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
      }
    }
  }).mount('#app')