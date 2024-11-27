using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace CRUDcontenidoWS.Models
{
    public class Producto
    {
        public string ISBN { get; set; }
        public string Autor { get; set; }
        public string Editorial { get; set; }
        public int Fecha { get; set; }
        public string Nombre { get; set; }
        public string Ruta { get; set; }
    }

    public class ProductoPostRequest
    {
        public string User { get; set; }
        public string Pass { get; set; }
        public string Categoria { get; set; }
        public string ProductoJSON { get; set; } 
    }

    public class ProductoPutRequest
    {
        public string User { get; set; }
        public string Pass { get; set; }
        public string ISBN { get; set; }
        public string Detalles { get; set; }
    }

    public class ProductoDeleteRequest
    {
        public string User { get; set; }
        public string Pass { get; set; }
        public string ISBN { get; set; }
    }

    public class Respuesta
    {
        public string Code { get; set; }
        public string Message { get; set; }
        public string Data { get; set; }
        public string Status { get; set; }
    }
}