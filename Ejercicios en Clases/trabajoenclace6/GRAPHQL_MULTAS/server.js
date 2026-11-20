
import express from "express";
import cors from "cors";
import { buildSchema } from "graphql";
import { createHandler } from "graphql-http";

const app = express();
const PUERTO = 4000;
const API_LARAVEL = "http://127.0.0.1:8000/api/multas";

app.use(cors());
app.use(express.json());

// ========================================
// COMUNICACION CON LARAVEL REST
// ========================================

async function consultarLaravel(url) {
    const respuesta = await fetch(url, {
        method: "GET",
        headers: {
            "Accept": "application/json"
        }
    });

    const contenido = await respuesta.json();

    if (!respuesta.ok) {
        throw new Error(
            contenido.message || "Error al consultar Laravel"
        );
    }

    if (contenido.success === false) {
        throw new Error(
            contenido.mensaje || "No se pudo consultar la multa"
        );
    }

    return contenido;
}

// ========================================
// NORMALIZAR MULTA
// ========================================

function convertirMulta(multa) {
    if (!multa) return null;

    return {
        id: multa.id,
        codigo: multa.codigo,
        ci: multa.ci,
        nombreConductor:
            multa.nombre_conductor ?? multa.nombreConductor,
        tipoInfraccion:
            multa.tipo_infraccion ?? multa.tipoInfraccion,
        descripcion: multa.descripcion,
        lugar: multa.lugar,
        fechaInfraccion:
            multa.fecha_infraccion ?? multa.fechaInfraccion,
        monto: Number(multa.monto),
        estado: multa.estado,
        policiaId: String(
            multa.policia_id ?? multa.policiaId ?? ""
        )
    };
}

// ========================================
// EXTRAER MULTA
// ========================================

function extraerMulta(respuesta) {
    let datos = respuesta.data ?? respuesta;

    if (datos && !Array.isArray(datos) && datos.data) {
        datos = datos.data;
    }

    return convertirMulta(datos);
}

// ========================================
// EXTRAER LISTA DE MULTAS
// ========================================

function extraerMultas(respuesta) {
    let datos = respuesta.data ?? respuesta;

    if (datos && !Array.isArray(datos) && Array.isArray(datos.data)) {
        datos = datos.data;
    }

    if (!Array.isArray(datos)) return [];

    return datos.map(convertirMulta);
}

// ========================================
// ESQUEMA GRAPHQL
// ========================================

const esquema = buildSchema(`
    type Multa {
        id: ID
        codigo: String
        ci: String
        nombreConductor: String
        tipoInfraccion: String
        descripcion: String
        lugar: String
        fechaInfraccion: String
        monto: Float
        estado: String
        policiaId: String
    }

    type VerificacionPago {
        existe: Boolean
        ciCorrecto: Boolean
        puedePagar: Boolean
        estado: String
        mensaje: String
        multa: Multa
    }

    type Query {
        consultarMulta(codigo: String!): Multa
        consultarPorCI(ci: String!): [Multa]
        verificarPago(codigo: String!, ci: String!): VerificacionPago
    }
`);

// ========================================
// RESOLVERS
// ========================================

const root = {

    consultarMulta: async ({ codigo }) => {
        const url =
            `${API_LARAVEL}/${encodeURIComponent(codigo)}`;

        const respuesta = await consultarLaravel(url);
        return extraerMulta(respuesta);
    },

    consultarPorCI: async ({ ci }) => {
        const url =
            `${API_LARAVEL}?ci=${encodeURIComponent(ci)}`;

        const respuesta = await consultarLaravel(url);
        return extraerMultas(respuesta);
    },

    verificarPago: async ({ codigo, ci }) => {
        const url =
            `${API_LARAVEL}/${encodeURIComponent(codigo)}`;

        const respuesta = await consultarLaravel(url);
        const multa = extraerMulta(respuesta);

        if (!multa) {
            return {
                existe: false,
                ciCorrecto: false,
                puedePagar: false,
                estado: "NO_EXISTE",
                mensaje: "La multa no existe",
                multa: null
            };
        }

        const ciCorrecto = String(multa.ci) === String(ci);
        const pendiente = multa.estado === "pendiente";

        let mensaje = "La multa puede pagarse";

        if (!ciCorrecto) {
            mensaje = "El CI no corresponde a esta multa";
        } else if (multa.estado === "pagada") {
            mensaje = "La multa ya fue pagada";
        } else if (multa.estado === "anulada") {
            mensaje = "La multa fue anulada";
        }

        return {
            existe: true,
            ciCorrecto,
            puedePagar: ciCorrecto && pendiente,
            estado: multa.estado,
            mensaje,
            multa
        };
    }
};

// ========================================
// RUTA GRAPHQL
// ========================================

app.all(
    "/graphql",
    createHandler({
        schema: esquema,
        rootValue: root
    })
);

// ========================================
// INICIAR SERVIDOR
// ========================================

app.listen(PUERTO, () => {
    console.log("");
    console.log("==================================");
    console.log("     SERVIDOR GRAPHQL - MULTAS");
    console.log("==================================");
    console.log(`Servidor: http://127.0.0.1:${PUERTO}`);
    console.log(`GraphQL: http://127.0.0.1:${PUERTO}/graphql`);
    console.log("Conectado a Laravel REST API");
    console.log("==================================");
    console.log("");
});
