document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const correo = document.getElementById("correo").value;
  const clave = document.getElementById("clave").value;

  const res = await fetch("api/login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ correo, clave })
  });

  const data = await res.json();
  if (data.success) {
    window.location.href = data.redirect;
  } else {
    alert(data.message);
  }
});
