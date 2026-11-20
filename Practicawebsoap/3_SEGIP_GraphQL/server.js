const express = require('express');
const { graphqlHTTP } = require('express-graphql');
const { buildSchema } = require('graphql');
const cors = require('cors');

// Definición del esquema GraphQL solicitado:
// recurso persona con campos: CI, Nombres, PrimerApellido, SegundoApellido
// y permitir buscar por CI
const schema = buildSchema(`
  type Persona {
    CI: String!
    Nombres: String!
    PrimerApellido: String!
    SegundoApellido: String!
  }

  type Query {
    obtenerPersona(CI: String!): Persona
    todasPersonas: [Persona]
  }
`);

// Base de datos en memoria para pruebas
const personasDB = [
  { CI: '1234567', Nombres: 'Juan', PrimerApellido: 'Perez', SegundoApellido: 'Gomez' },
  { CI: '7654321', Nombres: 'Maria', PrimerApellido: 'Lopez', SegundoApellido: 'Rojas' },
  { CI: '8888888', Nombres: 'Carlos', PrimerApellido: 'Fernandez', SegundoApellido: 'Quiroga' }
];

// Resolvers
const root = {
  obtenerPersona: ({ CI }) => {
    return personasDB.find(p => p.CI === CI) || null;
  },
  todasPersonas: () => personasDB
};

const app = express();
app.use(cors());

app.use('/graphql', graphqlHTTP({
  schema: schema,
  rootValue: root,
  graphiql: true // Habilita la interfaz GraphiQL interactiva en navegador
}));

const PORT = 5000;
app.listen(PORT, () => {
  console.log(`[SEGIP GraphQL] Servidor corriendo en http://localhost:${PORT}/graphql`);
});
