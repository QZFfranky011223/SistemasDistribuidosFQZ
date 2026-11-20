using System;
using System.Drawing;
using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Windows.Forms;
using System.Xml.Linq;

namespace PoliciaTransitoForms
{
    public class FormPolicia : Form
    {
        private TextBox txtSegipUrl = null!;
        private TextBox txtCentralUrl = null!;
        private TextBox txtCi = null!;
        private Button btnVerificarSegip = null!;
        private Label lblEstadoSegip = null!;

        private TextBox txtCodigo = null!;
        private TextBox txtNombre = null!;
        private TextBox txtPlaca = null!;
        private DateTimePicker dtpFecha = null!;
        private ComboBox cmbTipoInfraccion = null!;
        private TextBox txtLugar = null!;
        private NumericUpDown numMonto = null!;
        private Button btnRegistrarMulta = null!;
        private TextBox txtLog = null!;

        private bool ciValidadoEnSegip = false;
        private string ultimoCiValidado = "";
        private static readonly HttpClient httpClient = new HttpClient { Timeout = TimeSpan.FromSeconds(10) };

        public FormPolicia()
        {
            InitializeComponent();
        }

        private void InitializeComponent()
        {
            this.Text = "Policía Boliviana - Módulo de Emisión de Boletas de Infracción (C# Windows Forms)";
            this.Size = new Size(760, 680);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.BackColor = Color.FromArgb(241, 245, 249);
            this.Font = new Font("Segoe UI", 9.5F, FontStyle.Regular);

            // Cabecera
            Panel pnlHeader = new Panel
            {
                Dock = DockStyle.Top,
                Height = 64,
                BackColor = Color.FromArgb(15, 23, 42)
            };
            Label lblTitle = new Label
            {
                Text = "ORGANISMO OPERATIVO DE TRÁNSITO — EMISIÓN DE BOLETAS",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 12.5F, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(18, 12)
            };
            Label lblSubtitle = new Label
            {
                Text = "1. Validación SOAP con SEGIP (Bool verificarCI)   |   2. Registro REST en Central (Recurso: /api/multa)",
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Segoe UI", 8.5F, FontStyle.Regular),
                AutoSize = true,
                Location = new Point(18, 38)
            };
            pnlHeader.Controls.Add(lblTitle);
            pnlHeader.Controls.Add(lblSubtitle);
            this.Controls.Add(pnlHeader);

            // Configuración de Endpoints
            GroupBox grpConfig = new GroupBox
            {
                Text = "Configuración de Servicios Distribuidos",
                Location = new Point(16, 74),
                Size = new Size(710, 62),
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            Label lblSegip = new Label { Text = "SOAP SEGIP:", Location = new Point(12, 26), AutoSize = true, Font = new Font("Segoe UI", 9F) };
            txtSegipUrl = new TextBox { Text = "http://localhost:8001/server.php", Location = new Point(95, 23), Width = 240, Font = new Font("Segoe UI", 9F) };
            Label lblCentral = new Label { Text = "REST Central:", Location = new Point(350, 26), AutoSize = true, Font = new Font("Segoe UI", 9F) };
            txtCentralUrl = new TextBox { Text = "http://localhost:8000/api/multa", Location = new Point(435, 23), Width = 260, Font = new Font("Segoe UI", 9F) };
            grpConfig.Controls.AddRange(new Control[] { lblSegip, txtSegipUrl, lblCentral, txtCentralUrl });
            this.Controls.Add(grpConfig);

            // Paso 1: Validación SOAP con SEGIP
            GroupBox grpSegip = new GroupBox
            {
                Text = "Paso 1: Validar Identidad del Conductor en SEGIP (Protocolo SOAP)",
                Location = new Point(16, 144),
                Size = new Size(710, 88),
                Font = new Font("Segoe UI", 9.5F, FontStyle.Bold)
            };
            Label lblCi = new Label { Text = "CI del Conductor:", Location = new Point(15, 32), AutoSize = true, Font = new Font("Segoe UI", 9.5F) };
            txtCi = new TextBox { Text = "1234567", Location = new Point(135, 29), Width = 160, Font = new Font("Segoe UI", 10F) };
            txtCi.TextChanged += (s, e) =>
            {
                if (txtCi.Text.Trim() != ultimoCiValidado)
                {
                    ciValidadoEnSegip = false;
                    lblEstadoSegip.Text = "Estado SEGIP: Pendiente de verificación SOAP";
                    lblEstadoSegip.ForeColor = Color.FromArgb(180, 83, 9);
                    btnRegistrarMulta.Enabled = false;
                }
            };

            btnVerificarSegip = new Button
            {
                Text = "🔍 Consultar SOAP: verificarCI(ci)",
                Location = new Point(310, 26),
                Size = new Size(235, 34),
                BackColor = Color.FromArgb(2, 132, 199),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5F, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnVerificarSegip.Click += async (s, e) => await VerificarCiEnSegipAsync();

            lblEstadoSegip = new Label
            {
                Text = "Estado SEGIP: Debe validar el CI antes de emitir la boleta (CIs prueba: 1234567, 7654321; inválido: 0000000)",
                Location = new Point(15, 63),
                AutoSize = true,
                ForeColor = Color.FromArgb(180, 83, 9),
                Font = new Font("Segoe UI", 8.5F, FontStyle.Bold)
            };
            grpSegip.Controls.AddRange(new Control[] { lblCi, txtCi, btnVerificarSegip, lblEstadoSegip });
            this.Controls.Add(grpSegip);

            // Paso 2: Datos de la Boleta de Infracción (REST -> recurso multa)
            GroupBox grpBoleta = new GroupBox
            {
                Text = "Paso 2: Datos de la Boleta de Infracción (Servicio REST -> Recurso: multa)",
                Location = new Point(16, 240),
                Size = new Size(710, 225),
                Font = new Font("Segoe UI", 9.5F, FontStyle.Bold)
            };

            Label lblCodigo = new Label { Text = "Código Único:", Location = new Point(15, 34), AutoSize = true, Font = new Font("Segoe UI", 9.5F) };
            txtCodigo = new TextBox { Text = "BOL-" + new Random().Next(1002, 9999), Location = new Point(145, 31), Width = 175, Font = new Font("Segoe UI", 9.5F) };

            Button btnGenerarCod = new Button
            {
                Text = "Nuevo Código",
                Location = new Point(328, 30),
                Size = new Size(105, 27),
                Font = new Font("Segoe UI", 8.5F)
            };
            btnGenerarCod.Click += (s, e) => txtCodigo.Text = "BOL-" + new Random().Next(1000, 9999);

            Label lblPlaca = new Label { Text = "Placa Vehículo:", Location = new Point(450, 34), AutoSize = true, Font = new Font("Segoe UI", 9.5F) };
            txtPlaca = new TextBox { Text = "4521-TKR", Location = new Point(555, 31), Width = 135, Font = new Font("Segoe UI", 9.5F) };

            Label lblNombre = new Label { Text = "Nombre Conductor:", Location = new Point(15, 72), AutoSize = true, Font = new Font("Segoe UI", 9.5F) };
            txtNombre = new TextBox { Text = "Juan Pérez Mamani", Location = new Point(145, 69), Width = 288, Font = new Font("Segoe UI", 9.5F) };

            Label lblFecha = new Label { Text = "Fecha Infracción:", Location = new Point(450, 72), AutoSize = true, Font = new Font("Segoe UI", 9.5F) };
            dtpFecha = new DateTimePicker
            {
                Format = DateTimePickerFormat.Custom,
                CustomFormat = "yyyy-MM-dd",
                Location = new Point(555, 69),
                Width = 135,
                Font = new Font("Segoe UI", 9.5F)
            };

            Label lblTipo = new Label { Text = "Tipo de Infracción:", Location = new Point(15, 110), AutoSize = true, Font = new Font("Segoe UI", 9.5F) };
            cmbTipoInfraccion = new ComboBox
            {
                Location = new Point(145, 107),
                Width = 288,
                DropDownStyle = ComboBoxStyle.DropDown,
                Font = new Font("Segoe UI", 9.5F)
            };
            cmbTipoInfraccion.Items.AddRange(new object[]
            {
                "Exceso de velocidad en zona urbana",
                "Cruzar semáforo en luz roja",
                "Estacionamiento en lugar prohibido",
                "Conducir sin cinturón de seguridad",
                "Conducir usando teléfono móvil",
                "Invasión de carril exclusivo"
            });
            cmbTipoInfraccion.SelectedIndex = 0;
            cmbTipoInfraccion.SelectedIndexChanged += (s, e) =>
            {
                decimal[] montosSugeridos = { 250m, 350m, 150m, 100m, 200m, 300m };
                if (cmbTipoInfraccion.SelectedIndex >= 0 && cmbTipoInfraccion.SelectedIndex < montosSugeridos.Length)
                {
                    numMonto.Value = montosSugeridos[cmbTipoInfraccion.SelectedIndex];
                }
            };

            Label lblMonto = new Label { Text = "Monto Multa (Bs):", Location = new Point(445, 110), AutoSize = true, Font = new Font("Segoe UI", 9.5F) };
            numMonto = new NumericUpDown
            {
                Location = new Point(565, 107),
                Width = 125,
                Minimum = 10,
                Maximum = 100000,
                DecimalPlaces = 2,
                Value = 250.00m,
                Font = new Font("Segoe UI", 9.5F)
            };

            Label lblLugar = new Label { Text = "Lugar Infracción:", Location = new Point(15, 148), AutoSize = true, Font = new Font("Segoe UI", 9.5F) };
            txtLugar = new TextBox { Text = "Av. Mariscal Santa Cruz esq. Colón", Location = new Point(145, 145), Width = 545, Font = new Font("Segoe UI", 9.5F) };

            btnRegistrarMulta = new Button
            {
                Text = "🚓 Emitir y Registrar Boleta en Central de Registro (POST /api/multa)",
                Location = new Point(145, 180),
                Size = new Size(545, 34),
                BackColor = Color.FromArgb(22, 163, 74),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10F, FontStyle.Bold),
                Enabled = false,
                Cursor = Cursors.Hand
            };
            btnRegistrarMulta.Click += async (s, e) => await RegistrarMultaRestAsync();

            grpBoleta.Controls.AddRange(new Control[]
            {
                lblCodigo, txtCodigo, btnGenerarCod, lblPlaca, txtPlaca,
                lblNombre, txtNombre, lblFecha, dtpFecha,
                lblTipo, cmbTipoInfraccion, lblMonto, numMonto,
                lblLugar, txtLugar, btnRegistrarMulta
            });
            this.Controls.Add(grpBoleta);

            // Consola de Bitácora SOAP / REST
            GroupBox grpLog = new GroupBox
            {
                Text = "Bitácora de Comunicación Distribuida (Tramas SOAP XML y REST JSON)",
                Location = new Point(16, 473),
                Size = new Size(710, 155),
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            txtLog = new TextBox
            {
                Multiline = true,
                ScrollBars = ScrollBars.Vertical,
                ReadOnly = true,
                Dock = DockStyle.Fill,
                BackColor = Color.FromArgb(15, 23, 42),
                ForeColor = Color.FromArgb(56, 189, 248),
                Font = new Font("Consolas", 8.5F)
            };
            grpLog.Controls.Add(txtLog);
            this.Controls.Add(grpLog);

            Log("Módulo Policía de Tránsito iniciado. Primero valide el CI del conductor con el servicio SOAP del SEGIP.");
        }

        private void Log(string msg)
        {
            txtLog.AppendText($"[{DateTime.Now:HH:mm:ss}] {msg}{Environment.NewLine}");
        }

        /// <summary>
        /// Consume el servicio SOAP del SEGIP: Bool verificarCI(String ci)
        /// </summary>
        private async System.Threading.Tasks.Task VerificarCiEnSegipAsync()
        {
            string ci = txtCi.Text.Trim();
            if (string.IsNullOrEmpty(ci))
            {
                MessageBox.Show("Por favor ingrese el número de CI del conductor.", "SEGIP SOAP", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            string soapEnvelope = $@"<?xml version=""1.0"" encoding=""utf-8""?>
<soap:Envelope xmlns:soap=""http://schemas.xmlsoap.org/soap/envelope/""
               xmlns:tns=""http://segip.gob.bo/soap"">
  <soap:Body>
    <tns:verificarCI>
      <ci>{System.Security.SecurityElement.Escape(ci)}</ci>
    </tns:verificarCI>
  </soap:Body>
</soap:Envelope>";

            try
            {
                btnVerificarSegip.Enabled = false;
                Log($"[SOAP -> SEGIP] Enviando petición verificarCI(\"{ci}\") a {txtSegipUrl.Text}...");

                var request = new HttpRequestMessage(HttpMethod.Post, txtSegipUrl.Text.Trim());
                request.Headers.Add("SOAPAction", "http://segip.gob.bo/soap/verificarCI");
                request.Content = new StringContent(soapEnvelope, Encoding.UTF8, "text/xml");

                HttpResponseMessage response = await httpClient.SendAsync(request);
                string xmlResponse = await response.Content.ReadAsStringAsync();

                Log($"[SOAP <- SEGIP] Respuesta XML recibida: {xmlResponse.Replace("\r", "").Replace("\n", " ")}");

                XDocument doc = XDocument.Parse(xmlResponse);
                string resultVal = "false";
                string nombreSugerido = "";

                foreach (var el in doc.Descendants())
                {
                    if (el.Name.LocalName == "verificarCIResult" || el.Name.LocalName == "return")
                    {
                        resultVal = el.Value.Trim().ToLowerInvariant();
                    }
                    else if (el.Name.LocalName == "nombre")
                    {
                        nombreSugerido = el.Value.Trim();
                    }
                }

                bool esValido = (resultVal == "true" || resultVal == "1");
                ciValidadoEnSegip = esValido;
                ultimoCiValidado = ci;

                if (esValido)
                {
                    if (!string.IsNullOrEmpty(nombreSugerido) && nombreSugerido != "Ciudadano Verificado SEGIP")
                    {
                        txtNombre.Text = nombreSugerido;
                    }
                    lblEstadoSegip.Text = $"✅ SEGIP SOAP: verificarCI(\"{ci}\") = TRUE. Identidad verificada ({nombreSugerido}).";
                    lblEstadoSegip.ForeColor = Color.FromArgb(22, 163, 74);
                    btnRegistrarMulta.Enabled = true;
                }
                else
                {
                    lblEstadoSegip.Text = $"❌ SEGIP SOAP: verificarCI(\"{ci}\") = FALSE. El CI NO es válido en SEGIP. No se puede emitir boleta.";
                    lblEstadoSegip.ForeColor = Color.FromArgb(220, 38, 38);
                    btnRegistrarMulta.Enabled = false;
                    MessageBox.Show($"El SEGIP devolvió FALSE para el CI '{ci}'.\nNo está permitido emitir la boleta.", "Identidad No Validada", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
            catch (Exception ex)
            {
                Log($"[ERROR SOAP SEGIP] {ex.Message}");
                MessageBox.Show($"Error al conectar con el servicio SOAP del SEGIP en {txtSegipUrl.Text}:\n{ex.Message}", "Error SOAP", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            finally
            {
                btnVerificarSegip.Enabled = true;
            }
        }

        /// <summary>
        /// Registra la boleta en el Sistema Central de Registro usando el servicio REST (recurso: multa)
        /// </summary>
        private async System.Threading.Tasks.Task RegistrarMultaRestAsync()
        {
            if (!ciValidadoEnSegip || txtCi.Text.Trim() != ultimoCiValidado)
            {
                MessageBox.Show("Primero debe validar el CI del conductor con el servicio SOAP del SEGIP.", "Validación Requerida", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            var payload = new
            {
                codigo_unico = txtCodigo.Text.Trim(),
                ci_conductor = txtCi.Text.Trim(),
                nombre_conductor = txtNombre.Text.Trim(),
                placa = txtPlaca.Text.Trim(),
                fecha = dtpFecha.Value.ToString("yyyy-MM-dd"),
                tipo_infraccion = cmbTipoInfraccion.Text.Trim(),
                lugar = txtLugar.Text.Trim(),
                monto = numMonto.Value
            };

            string json = JsonSerializer.Serialize(payload);

            try
            {
                btnRegistrarMulta.Enabled = false;
                Log($"[REST -> Central] POST {txtCentralUrl.Text} Payload: {json}");

                var content = new StringContent(json, Encoding.UTF8, "application/json");
                var request = new HttpRequestMessage(HttpMethod.Post, txtCentralUrl.Text.Trim())
                {
                    Content = content
                };
                request.Headers.Add("Accept", "application/json");

                HttpResponseMessage response = await httpClient.SendAsync(request);
                string respBody = await response.Content.ReadAsStringAsync();

                Log($"[REST <- Central] HTTP {(int)response.StatusCode}: {respBody}");

                if (response.IsSuccessStatusCode)
                {
                    MessageBox.Show(
                        $"¡Boleta de infracción emitida y registrada exitosamente en la Central de Registro!\n\n" +
                        $"Código Único: {payload.codigo_unico}\n" +
                        $"Conductor: {payload.nombre_conductor} (CI: {payload.ci_conductor})\n" +
                        $"Infracción: {payload.tipo_infraccion}\n" +
                        $"Monto: Bs. {payload.monto:F2}\n" +
                        $"Estado inicial: pendiente",
                        "Boleta Registrada (REST /api/multa)",
                        MessageBoxButtons.OK,
                        MessageBoxIcon.Information
                    );
                    txtCodigo.Text = "BOL-" + new Random().Next(1000, 9999);
                }
                else
                {
                    MessageBox.Show($"La Central de Registro devolvió un error (HTTP {(int)response.StatusCode}):\n{respBody}", "Error REST", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
            catch (Exception ex)
            {
                Log($"[ERROR REST Central] {ex.Message}");
                MessageBox.Show($"Error al conectar con el servicio REST de la Central ({txtCentralUrl.Text}):\n{ex.Message}", "Error REST", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            finally
            {
                btnRegistrarMulta.Enabled = ciValidadoEnSegip;
            }
        }
    }
}
