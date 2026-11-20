const express = require('express');
const { graphqlHTTP } = require('express-graphql');
const { buildSchema } = require('graphql');
const cors = require('cors');

// 1. Esquema GraphQL
const schema = buildSchema(`
  type Persona {
    CI: String!
    Nombres: String!
    PrimerApellido: String!
    SegundoApellido: String!
  }

  type Query {
    obtenerPersona(CI: String!): Persona
  }
`);

// 2. Base de datos simulada del SEGIP
const personas = [
  {
    CI: "1234567",
    Nombres: "Juan",
    PrimerApellido: "Perez",
    SegundoApellido: "Gomez"
  },
  {
    CI: "7654321",
    Nombres: "Maria",
    PrimerApellido: "Lopez",
    SegundoApellido: "Rojas"
  },
  {
    CI: "5555555",
    Nombres: "Carlos",
    PrimerApellido: "Mamani",
    SegundoApellido: "Flores"
  }
];

// 3. Resolver
const root = {
  obtenerPersona: ({ CI }) => {
    const encontrada = personas.find(p => p.CI === CI);
    return encontrada || null;
  }
};

const app = express();
app.use(cors());

// 4. Ruta GraphQL
app.use('/graphql', graphqlHTTP({
  schema: schema,
  rootValue: root,
  graphiql: true
}));

const PORT = 4000;
app.listen(PORT, () => {
  console.log(`Servidor SEGIP GraphQL activo en: http://localhost:${PORT}/graphql`);
});