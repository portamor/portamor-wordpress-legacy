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
            img:  'https://www.w3schools.com/howto/img_avatar.png'
          },
          {
            id: 2,
            name: 'Maria Carde',
            proffession: 'Psicologa',
            number: '999999999',
            img: 'https://www.w3schools.com/howto/img_avatar.png'
          },
          {
            id: 3,
            name: 'Medali',
            proffession: 'Enfermera',
            number: '999999999',
            img: 'https://www.w3schools.com/howto/img_avatar.png'
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
    img:  'https://www.w3schools.com/howto/img_avatar.png',
    category: 1
  },
  {
    id: 2,
    name: 'Maria Carde',
    proffession: 'Psicologa',
    number: '999999999',
    img: 'https://www.w3schools.com/howto/img_avatar.png',
    category: 1
  },
  {
    id: 3,
    name: 'Medali',
    proffession: 'Enfermera',
    number: '999999999',
    img: 'https://www.w3schools.com/howto/img_avatar.png',
    category: 2
  },
  {
    id: 3,
    name: 'Marcelo',
    proffession: 'Enfermera',
    number: '999999999',
    img: 'https://www.w3schools.com/howto/img_avatar.png',
    category: 3
  },
  {
    id: 3,
    name: 'José Luis',
    proffession: 'Ingeniero',
    number: '999999999',
    img: 'https://www.w3schools.com/howto/img_avatar.png',
    category: 2
  },
  {
    id: 3,
    name: 'Antoni',
    proffession: 'Medico',
    number: '999999999',
    img: 'https://www.w3schools.com/howto/img_avatar.png',
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
        console.log(id);
        this.specialists = specialistlist.filter((specialist) => specialist.category == id);
      }
    }
  }).mount('#app')