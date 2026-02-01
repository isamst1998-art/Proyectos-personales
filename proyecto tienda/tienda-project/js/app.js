// ================= DATOS INICIALES =================
const products = [
  { id: 1, 
    title: "NESTEA LIMON", 
    desc: "1 LITRO", 
    price: 2 },

  { id: 2, 
    title: "NESTEA MARACUYA", 
    desc: "1 LITRO", 
    price: 2 },

  { id: 3, 
    title: "NESTEA LIMON", 
    desc: "LATA 250 ML", 
    price: 1.20 },

  { id: 4, 
    title: "NESTEA MARACUYA", 
    desc: "LATA 250 ML", 
    price: 1.20 },

  { id: 5, 
    title: "COCA COLA", 
    desc: "LATA 250 ML", 
    price: 1.20 },

  { id: 6, 
    title: "COCA COLA CERO", 
    desc: "LATA 250 ML", 
    price: 1.20 },

  { id: 7, 
    title: "COCA COLA CERO CERO", 
    desc: "LATA 250 ML", 
    price: 1.20 },

  { id: 8, 
    title: "COCA COLA", 
    desc: "1 LITRO", 
    price: 2 },

  { id: 9, 
    title: "COCA COLA CERO", 
    desc: "1 LITRO", 
    price: 2 },

  { id: 10, 
    title: "COCA COLA CERO CERO", 
    desc: "1 LITRO", 
    price: 2 }
];


// ================= UTILIDADES =================
function getData(key) {
  return JSON.parse(localStorage.getItem(key)) || [];
}
function setData(key, value) {
  localStorage.setItem(key, JSON.stringify(value));
}

// ================= INIT =================
document.addEventListener("DOMContentLoaded", () => {
  crearProveedor();
  renderProducts();
  renderMyOrders();
  renderProviderOrders();

  const btnLogin = document.getElementById("btnLogin");
  if (btnLogin) btnLogin.onclick = login;

  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", e => {
      renderProducts(e.target.value);
    });
  }
});

// ================= LOGIN =================
function crearProveedor() {
  let users = getData("users");
  if (!users.some(u => u.email === "proveedor@tienda.com")) {
    users.push({
      name: "Proveedor",
      empresa: "Central",
      direccion: "Almacén central",
      localidad: "Lebrija",
      email: "proveedor@tienda.com",
      password: "proveedor123",
      role: "provider"
    });
    setData("users", users);
  }
}

function login() {
  const email = loginEmail.value.trim();
  const password = loginPassword.value.trim();

  const user = getData("users")
    .find(u => u.email === email && u.password === password);

  if (!user) return alert("Credenciales incorrectas");

  localStorage.setItem("session", JSON.stringify(user));
  location.href = user.role === "provider" ? "provider.html" : "index.html";
}

// ================= PRODUCTOS =================
function renderProducts(filter = "") {
  const grid = document.getElementById("productsGrid");
  if (!grid) return;

  const list = products.filter(p =>
    (p.title + p.desc).toLowerCase().includes(filter.toLowerCase())
  );

  grid.innerHTML = list.map(p => `
    <div class="card">
      <div class="card-body">
        <h3>${p.title}</h3>
        <p>${p.desc}</p>
        <div class="price">${p.price.toFixed(2)} €</div>
        <button class="btn" onclick="addOrder(${p.id})">Añadir</button>
      </div>
    </div>
  `).join("");
}

// ================= AÑADIR PEDIDO =================
function addOrder(id) {
  const session = JSON.parse(localStorage.getItem("session"));
  if (!session) return alert("Debes iniciar sesión");

  let orders = getData("orders");
  const product = products.find(p => p.id === id);

  let order = orders.find(o =>
    o.cliente === session.email &&
    o.producto === product.title &&
    o.estado === "Pendiente"
  );

  if (order) {
    order.cantidad++;
  } else {
    orders.push({
      id: Date.now(),
      cliente: session.email,
      nombre: session.name,
      empresa: session.empresa,
      direccion: session.direccion,
      localidad: session.localidad,
      producto: product.title,
      precio: product.price,
      cantidad: 1,
      estado: "Pendiente"
    });
  }

  setData("orders", orders);
  alert("Producto añadido a Mis pedidos");
  renderMyOrders();
}

// ================= MIS PEDIDOS =================
function renderMyOrders() {
  const box = document.getElementById("ordersContainer");
  if (!box) return;

  const session = JSON.parse(localStorage.getItem("session"));
  if (!session) return;

  const orders = getData("orders").filter(o => o.cliente === session.email);
  if (!orders.length) {
    box.innerHTML = "<p>No tienes pedidos</p>";
    return;
  }

  let total = 0;
  let showBtn = false;

  box.innerHTML =
    orders.map(o => {
      total += o.precio * o.cantidad;
      if (o.estado === "Pendiente") showBtn = true;

      return `
        <div class="order">
          <strong>${o.producto}</strong><br>
          Cantidad: ${o.cantidad}<br>
          Subtotal: ${(o.precio * o.cantidad).toFixed(2)} €<br>
          <strong>Estado:</strong> ${o.estado}
        </div>
      `;
    }).join("") +
    `
      <hr>
      <h3>Total: ${total.toFixed(2)} €</h3>
      ${showBtn ? `<button class="btn primary" onclick="sendOrder()">Tramitar pedido</button>` : ""}
    `;
}

// ================= TRAMITAR =================
function sendOrder() {
  let orders = getData("orders");
  orders.forEach(o => {
    if (o.estado === "Pendiente") o.estado = "Confirmado";
  });
  setData("orders", orders);
  renderMyOrders();
}

// ================= PANEL REPARTIDOR =================
function renderProviderOrders() {
  const box = document.getElementById("providerOrders");
  if (!box) return;

  const orders = getData("orders");
  if (!orders.length) {
    box.innerHTML = "<p>No hay pedidos</p>";
    return;
  }

  const grouped = {};
  orders.forEach(o => {
    if (!grouped[o.cliente]) grouped[o.cliente] = [];
    grouped[o.cliente].push(o);
  });

  box.innerHTML = Object.values(grouped).map(list => {
    const c = list[0];

    return `
      <div class="order client-box">
        <h3>${c.nombre}</h3>
        <p>${c.direccion}, ${c.localidad}</p>
        <p><strong>Estado:</strong> ${c.estado}</p>

        <hr>

        ${list.map(o => `
          <div>• ${o.producto} x ${o.cantidad}</div>
        `).join("")}

        <br>
        <button onclick="setStatus('${c.cliente}','En reparto')">En reparto</button>
        <button onclick="setStatus('${c.cliente}','Entregado')">Entregado</button>
      </div>
    `;
  }).join("");
}

function setStatus(cliente, estado) {
  let orders = getData("orders");

  if (estado === "Entregado") {
    orders = orders.filter(o => o.cliente !== cliente);
  } else {
    orders.forEach(o => {
      if (o.cliente === cliente) o.estado = estado;
    });
  }

  setData("orders", orders);
  renderProviderOrders();
}
