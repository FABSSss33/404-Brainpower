<style>
  .registro-container {
    background: #0c4953;
    padding: 30px;
    border-radius: 10px;
    width: 350px;
    margin: 40px auto;
    box-shadow: 0 0 15px #217277;
  }
  .registro-container h2 {
    color: #42aab1;
    margin-bottom: 25px;
    text-align: center;
    font-family: Arial, sans-serif;
  }
  .registro-container label {
    display: block;
    font-family: Arial, sans-serif;
    color: #5c8888;
    margin-bottom: 6px;
  }
  .registro-container input[type="text"],
  .registro-container input[type="email"],
  .registro-container input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 18px;
    border-radius: 6px;
    border: none;
    background-color: #3e947c;
    color: #e0f0eb;
    font-size: 14px;
  }
  .registro-container button {
    width: 100%;
    background-color: #1c839f;
    color: white;
    padding: 12px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-family: Arial, sans-serif;
    transition: background-color 0.3s ease;
  }
  .registro-container button:hover {
    background-color: #217277;
  }
</style>

<div class="registro-container">
  <h2>Registro de Usuario</h2>
  <form action="registro-proceso.php" method="POST">
    <label for="nombre">Nombre completo</label>
    <input type="text" name="nombre" id="nombre" placeholder="Escribe tu nombre" required />
    
    <label for="correo">Correo electrónico</label>
    <input type="email" name="correo" id="correo" placeholder="ejemplo@correo.com" required />
    
    <label for="password">Contraseña</label>
    <input type="password" name="password" id="password" placeholder="Contraseña segura" required />
    
    <button type="submit">Registrar</button>
  </form>
</div>
