using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Web.Http;
using System.Text;

using CRUDcontenidoWS.Models;
using FireSharp.Config;
using FireSharp.Interfaces;
using FireSharp.Response;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System.Security.Cryptography;

namespace CRUDcontenidoWS.Controllers
{
    public class ProductosController : ApiController
    {
        IFirebaseConfig config = new FirebaseConfig
        {
            AuthSecret = "kvIqtvSCgr1Qe3VEjTNBZHPz6JZLgEFgis84dzjB",
            BasePath = "https://productosws-743c8-default-rtdb.firebaseio.com/"
        };

        public static string GenerarMd5(string input)
        {
            using (MD5 md5 = MD5.Create())
            {
                // Convertir la cadena de entrada a bytes y calcular el hash MD5
                byte[] inputBytes = Encoding.ASCII.GetBytes(input);
                byte[] hashBytes = md5.ComputeHash(inputBytes);

                // Convertir el hash a cadena hexadecimal
                return BitConverter.ToString(hashBytes).Replace("-", "").ToLower();
            }
        }

        // POST: api/Productos
        [HttpPost]
        [Route("api/Productos/createProd")]
        public IHttpActionResult Post([FromBody] ProductoPostRequest productoPostRequest)
        {
            // Acceder a los valores del objeto ProductoPostRequest
            var user = productoPostRequest.User;
            var pass = productoPostRequest.Pass;
            var categoria = productoPostRequest.Categoria;
            var productoJSON = productoPostRequest.ProductoJSON;

            Respuesta RespuestaSetProd = new Respuesta();
            RespuestaSetProd.Data = "";
            RespuestaSetProd.Status = "fail";
            Producto producto = new Producto();
            dynamic jsonObject;
            IFirebaseClient client = new FireSharp.FirebaseClient(config);

            if (client != null)
            {
                FirebaseResponse usuario = client.Get("usuarios/" + user);
                if (usuario.Body == "null")
                {
                    RespuestaSetProd.Code = "500";
                    RespuestaSetProd.Message = client.Get("respuestas/500").Body;
                }
                else
                {
                    FirebaseResponse contrasena = client.Get("usuarios/" + user);
                    if (contrasena.Body == "\"" + GenerarMd5(pass) + "\"")
                    {
                        try
                        {
                            jsonObject = JsonConvert.DeserializeObject(productoJSON);
                            foreach (var obj in jsonObject)
                            {
                                producto.ISBN = obj.Name;
                                producto.Nombre = obj.Value;
                            }
                        }
                        catch (JsonException)
                        {
                            RespuestaSetProd.Code = "303";
                            RespuestaSetProd.Message = client.Get("respuestas/303").Body;
                            return Ok(RespuestaSetProd);
                        }

                        FirebaseResponse existeD = client.Get("detalles/" + producto.ISBN);
                        FirebaseResponse existeP = client.Get("productos/" + categoria + "/" + producto.ISBN);
                        if (existeP.Body == "null" && existeD.Body == "null")
                        {
                            SetResponse responseD = client.Set("detalles/" + producto.ISBN, new { Nombre = producto.Nombre });
                            SetResponse responseP = client.Set("productos/" + categoria + "/" + producto.ISBN, producto.Nombre);
                            RespuestaSetProd.Code = "202";
                            RespuestaSetProd.Message = client.Get("respuestas/202").Body;
                            RespuestaSetProd.Data = DateTime.Now.ToString("yyyy-MM-ddTHH:mm:ss");
                            RespuestaSetProd.Status = "success";

                            var data = new
                            {
                                ISBN = producto.ISBN,
                                Nombre = producto.Nombre,
                                User = user,
                                Categoria = categoria,
                                FechaRegistro = DateTime.Now.ToString("yyyy-MM-ddTHH:mm:ss"),
                                type = 2
                            };
                            SendWebhook(data);
                        }
                        else
                        {
                            RespuestaSetProd.Code = "302";
                            RespuestaSetProd.Message = client.Get("respuestas/302").Body;
                        }
                    }
                    else
                    {
                        RespuestaSetProd.Code = "501";
                        RespuestaSetProd.Message = client.Get("respuestas/501").Body;
                    }
                }
            }
            else
            {
                RespuestaSetProd.Code = "000";
                RespuestaSetProd.Message = "Error en la conexion";
            }
            return Ok(RespuestaSetProd);
        }

        // Método para enviar datos al webhook
        private void SendWebhook(object data, string webhookUrl = "http://localhost/ws/proyecto/webhook.php")
        {
            using (HttpClient client = new HttpClient())
            {
                try
                {
                    string jsonData = JsonConvert.SerializeObject(data);
                    var content = new StringContent(jsonData, Encoding.UTF8, "application/json");
                    var response = client.PostAsync(webhookUrl, content).Result;
                    if (response.IsSuccessStatusCode)
                    {
                        Console.WriteLine("Webhook enviado con éxito");
                    }
                    else
                    {
                        Console.WriteLine($"Error al enviar el webhook: {response.StatusCode}");
                    }
                }
                catch (Exception ex)
                {
                    Console.WriteLine($"Excepción al enviar el webhook: {ex.Message}");
                }
            }
        }

        // PUT: api/Productos
        [HttpPut]
        [Route("api/Productos/updateProd")]
        public IHttpActionResult Put([FromBody] ProductoPutRequest productoPutRequest)
        {
            // Acceder a los valores del objeto ProductoPutRequest
            var user = productoPutRequest.User;
            var pass = productoPutRequest.Pass;
            var isbn = productoPutRequest.ISBN;
            var detalles = productoPutRequest.Detalles;

            Respuesta RespuestaUpdateProd = new Respuesta();
            RespuestaUpdateProd.Data = "";
            RespuestaUpdateProd.Status = "fail";
            Producto producto = new Producto();
            IFirebaseClient client = new FireSharp.FirebaseClient(config);

            if (client != null)
            {
                FirebaseResponse usuario = client.Get("usuarios/" + user);
                if (usuario.Body == "null")
                {
                    RespuestaUpdateProd.Code = "500";
                    RespuestaUpdateProd.Message = client.Get("respuestas/500").Body;
                }
                else
                {
                    FirebaseResponse contrasena = client.Get("usuarios/" + user);
                    if (contrasena.Body == "\"" + GenerarMd5(pass) + "\"")
                    {
                        try
                        {
                            // Deserializar el JSON directamente a un objeto Producto
                            producto = JsonConvert.DeserializeObject<Producto>(detalles);
                        }
                        catch (JsonException)
                        {
                            RespuestaUpdateProd.Code = "303";
                            RespuestaUpdateProd.Message = client.Get("respuestas/303").Body;
                            return Ok(RespuestaUpdateProd);
                        }
                        string categoria = " ";
                        if (isbn.Substring(0, 3).Equals("COM", StringComparison.OrdinalIgnoreCase)) { categoria = "comics"; }
                        if (isbn.Substring(0, 3).Equals("LIB", StringComparison.OrdinalIgnoreCase)) { categoria = "libros"; }
                        if (isbn.Substring(0, 3).Equals("MAN", StringComparison.OrdinalIgnoreCase)) { categoria = "mangas"; }
                        if (isbn.Substring(0, 3).Equals("REV", StringComparison.OrdinalIgnoreCase)) { categoria = "revistas"; }
                        FirebaseResponse existeD = client.Get("detalles/" + isbn);
                        FirebaseResponse existeP = client.Get("productos/" + categoria + "/" + isbn);
                        if (existeP.Body != "null" && existeD.Body != "null")
                        {
                            FirebaseResponse responseP = client.Set("productos/" + categoria + "/" + isbn, producto.Nombre);
                            FirebaseResponse responseD = client.Update("detalles/" + isbn, producto);
                            RespuestaUpdateProd.Code = "203";
                            RespuestaUpdateProd.Message = client.Get("respuestas/203").Body;
                            RespuestaUpdateProd.Data = DateTime.Now.ToString("yyyy-MM-ddTHH:mm:ss");
                            RespuestaUpdateProd.Status = "success";
                        }
                        else
                        {
                            RespuestaUpdateProd.Code = "304";
                            RespuestaUpdateProd.Message = client.Get("respuestas/304").Body;
                        }
                    }
                    else
                    {
                        RespuestaUpdateProd.Code = "501";
                        RespuestaUpdateProd.Message = client.Get("respuestas/501").Body;
                    }
                }
            }
            else
            {
                RespuestaUpdateProd.Code = "000";
                RespuestaUpdateProd.Message = "Error en la conexion";
            }
            return Ok(RespuestaUpdateProd);
        }

        // DELETE: api/Productos
        [HttpDelete]
        [Route("api/Productos/deleteProd")]
        public IHttpActionResult Delete([FromBody] ProductoDeleteRequest productoDeleteRequest)
        {
            // Acceder a los valores del objeto ProductoDeleteRequest
            var user = productoDeleteRequest.User;
            var pass = productoDeleteRequest.Pass;
            var isbn = productoDeleteRequest.ISBN;

            Respuesta RespuestaDeleteProd = new Respuesta();
            RespuestaDeleteProd.Data = "";
            RespuestaDeleteProd.Status = "fail";
            IFirebaseClient client = new FireSharp.FirebaseClient(config);

            if (client != null)
            {
                FirebaseResponse usuario = client.Get("usuarios/" + user);
                if (usuario.Body == "null")
                {
                    RespuestaDeleteProd.Code = "500";
                    RespuestaDeleteProd.Message = client.Get("respuestas/500").Body;
                }
                else
                {
                    FirebaseResponse contrasena = client.Get("usuarios/" + user);
                    if (contrasena.Body == "\"" + GenerarMd5(pass) + "\"")
                    {
                        string categoria = " ";
                        if (isbn.Substring(0, 3).Equals("COM", StringComparison.OrdinalIgnoreCase)) { categoria = "comics"; }
                        if (isbn.Substring(0, 3).Equals("LIB", StringComparison.OrdinalIgnoreCase)) { categoria = "libros"; }
                        if (isbn.Substring(0, 3).Equals("MAN", StringComparison.OrdinalIgnoreCase)) { categoria = "mangas"; }
                        if (isbn.Substring(0, 3).Equals("REV", StringComparison.OrdinalIgnoreCase)) { categoria = "revistas"; }
                        FirebaseResponse existeD = client.Get("detalles/" + isbn);
                        FirebaseResponse existeP = client.Get("productos/" + categoria + "/" + isbn);
                        if (existeP.Body != "null" && existeD.Body != "null")
                        {
                            FirebaseResponse responseP = client.Delete("productos/" + categoria + "/" + isbn);
                            FirebaseResponse responseD = client.Delete("detalles/" + isbn);
                            RespuestaDeleteProd.Code = "204";
                            RespuestaDeleteProd.Message = client.Get("respuestas/204").Body;
                            RespuestaDeleteProd.Data = DateTime.Now.ToString("yyyy-MM-ddTHH:mm:ss");
                            RespuestaDeleteProd.Status = "success";
                        }
                        else
                        {
                            RespuestaDeleteProd.Code = "304";
                            RespuestaDeleteProd.Message = client.Get("respuestas/304").Body;
                        }
                    }
                    else
                    {
                        RespuestaDeleteProd.Code = "501";
                        RespuestaDeleteProd.Message = client.Get("respuestas/501").Body;
                    }
                }
            }
            else
            {
                RespuestaDeleteProd.Code = "000";
                RespuestaDeleteProd.Message = "Error en la conexion";
            }
            return Ok(RespuestaDeleteProd);
        }

        // GET: api/Productos
        [HttpGet]
        [Route("api/Productos/checkSub/{user}")]
        public IHttpActionResult Get(string user)
        {
            if (user == "") { user = "---"; }
            Respuesta RespuestaCheckSub = new Respuesta();
            RespuestaCheckSub.Data = "";
            RespuestaCheckSub.Status = "fail";
            IFirebaseClient client = new FireSharp.FirebaseClient(config);

            if (client != null)
            {
                FirebaseResponse usuario = client.Get("usuarios/" + user);
                if (usuario.Body == "null" && !string.IsNullOrEmpty(usuario.Body))
                {
                    RespuestaCheckSub.Code = "500";
                    RespuestaCheckSub.Message = client.Get("respuestas/500").Body;
                }
                else
                {
                    FirebaseResponse subID = client.Get("suscripciones/" + user);
                    if (subID.Body != "null" && !string.IsNullOrEmpty(subID.Body))
                    {
                        FirebaseResponse sub = client.Get("suscripciones/" + user + "/Fin/");
                        DateTime fechaFin = DateTime.ParseExact(sub.Body.ToString().Replace("\"", ""), "dd-MM-yyyy", null);
                        string fecha = DateTime.Today.ToString("dd-MM-yyyy");
                        DateTime fechaActual = DateTime.ParseExact(fecha, "dd-MM-yyyy", null);
                        if (fechaActual > fechaFin)
                        {
                            RespuestaCheckSub.Code = "401";
                            RespuestaCheckSub.Message = client.Get("respuestas/401").Body;
                        }
                        else
                        {
                            RespuestaCheckSub.Code = "403";
                            RespuestaCheckSub.Message = client.Get("respuestas/403").Body;
                            RespuestaCheckSub.Data = "";
                            RespuestaCheckSub.Status = "success";
                        }
                    }
                    else
                    {
                        RespuestaCheckSub.Code = "400";
                        RespuestaCheckSub.Message = client.Get("respuestas/400").Body;
                    }
                }
                // Enviar datos al webhook
                var dataToSend = new
                {
                    User = user,
                    Code = RespuestaCheckSub.Code,
                    Message = RespuestaCheckSub.Message,
                    Status = RespuestaCheckSub.Status,
                    FechaConsulta = DateTime.Now.ToString("yyyy-MM-ddTHH:mm:ss"),
                    type = 3
                };
                SendWebhook(dataToSend);
            }
            else
            {
                RespuestaCheckSub.Code = "000";
                RespuestaCheckSub.Message = "Error en la conexion";
            }
            return Ok(RespuestaCheckSub);
        }

        // GET: api/Productos
        [HttpGet]
        [Route("api/Productos/showProds/{categoria}/{isbn}")]
        public IHttpActionResult Get(string categoria, string isbn)
        {
            if (categoria == "mangas") { categoria = "MAN"; }
            if (categoria == "libros") { categoria = "LIB"; }
            if (categoria == "comics") { categoria = "COM"; }
            if (categoria == "revistas") { categoria = "REV"; }
            Respuesta RespuestaShowProd = new Respuesta();
            JObject registrosFiltrados = new JObject();
            IFirebaseClient client = new FireSharp.FirebaseClient(config);
            FirebaseResponse contenido = client.Get("detalles/");

            JObject registros = JObject.Parse(contenido.Body);

            foreach (var registro in registros.Properties())
            {
                // Obtener la clave de cada registro
                string claveCompleta = registro.Name;

                // Separar la clave en fecha y claveEmpleado
                var partes = claveCompleta.Split('0');
                string tipo = partes[0];

                if ((string.IsNullOrEmpty(categoria) || tipo == categoria || categoria == "0") && (string.IsNullOrEmpty(isbn) || claveCompleta == isbn || isbn == "0"))
                {
                    registrosFiltrados[claveCompleta] = registro.Value;
                }
            }
            string datos = registrosFiltrados.ToString();
            if (datos == "{}")
            {
                RespuestaShowProd.Code = "305";
                RespuestaShowProd.Message = client.Get("respuestas/305").Body;
                RespuestaShowProd.Data = datos;
                RespuestaShowProd.Status = "fail";
            }
            else
            {
                RespuestaShowProd.Message = client.Get("respuestas/205").Body;
                RespuestaShowProd.Code = "205";
                RespuestaShowProd.Data = datos;
                RespuestaShowProd.Status = "success";
            }

            return Ok(RespuestaShowProd);
        }
    }
}
