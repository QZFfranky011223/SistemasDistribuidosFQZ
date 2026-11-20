const express = require('express');
const { graphqlHTTP } = require('express-graphql');
const { buildSchema } = require('graphql');
const cors = require('cors');

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

const personas = [
  { CI: "1234567", Nombres: "Juan", PrimerApellido: "Perez", SegundoApellido: "Gomez" },
  { CI: "7654321", Nombres: "Maria", PrimerApellido: "Lopez", SegundoApellido: "Rojas" },
  { CI: "5555555", Nombres: "Carlos", PrimerApellido: "Mamani", SegundoApellido: "Flores" }
];

const root = {
  obtenerPersona: ({ CI }) => {
    return personas.find(p => p.CI === CI) || null;
  }
};

const app = express();
app.use(cors());

app.use('/graphql', graphqlHTTP({
  schema: schema,
  rootValue: root,
  graphiql: true
}));

const PORT = 4000;
app.listen(PORT, () => {
  console.log(`Servidor SEGIP GraphQL activo en: http://localhost:${PORT}/graphql`);
});
