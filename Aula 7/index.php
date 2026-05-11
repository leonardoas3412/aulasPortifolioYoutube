<?php
// Configuração do Database
$host = 'localhost';
$dbname = 'db_sistema1';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: ". $e->getMessage());
}

// Update - Preparação (Preenche os dados no formulário)
$id_edicao = '';
$nome_edicao = '';
$email_edicao = '';

if (isset($_GET['editar'])) {
    $id_edicao_atual = $_GET['editar'];
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_edicao_atual]);
    $usuario_edicao = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($usuario_edicao) {
        $id_edicao = $usuario_edicao['id'];
        $nome_edicao = $usuario_edicao['nome'];
        $email_edicao = $usuario_edicao['email'];
   }
}

// CREATE e UPDATE (Ação de Salvar)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['salvar'])) {
    $id_form = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];

    // Update em si
    if (!empty($id_form)) {
        $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $id_form]);
    } else {
        // Create em si
        $data_cadastro = date('Y-m-d');
        $sql = "INSERT INTO usuarios (nome, email, data_cad) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $data_cadastro]);
    }
    
    // Redireciona para limpar os dados da requisição e evitar duplo envio
    header("Location: index.php");
    exit;
}

// Delete
if (isset($_GET['deleta'])) {
    $id = $_GET['deleta'];
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}

// Read (Listagem de usuários)
$sql = "SELECT * FROM usuarios";
$stmt = $pdo->query($sql);
$usuarios_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD - Usuário</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .form-group { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2><?= empty($id_edicao) ? 'Cadastro de usuário' : 'Editando usuário' ?></h2>
    
    <form method="POST" action="index.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id_edicao) ?>">
        
        <div class="form-group">
            <label>Nome:</label><br>
            <input type="text" name="nome" value="<?= htmlspecialchars($nome_edicao) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($email_edicao) ?>" required>
        </div>
        
        <input type="submit" name="salvar" value="OK">
    </form>
    
    <hr>
    
    <h2>Lista de usuários cadastrados</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Data de cadastro</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($usuarios_lista as $usuario): ?>
        <tr>
            <td><?= htmlspecialchars($usuario['id']) ?></td>
            <td><?= htmlspecialchars($usuario['nome']) ?></td>
            <td><?= htmlspecialchars($usuario['email']) ?></td>
            <td><?= htmlspecialchars($usuario['data_cad']) ?></td>
            <td>
                <a href="?editar=<?= $usuario['id'] ?>">Editar</a> | 
                <a href="?deleta=<?= $usuario['id'] ?>" onclick="return confirm('Tem certeza que deseja deletar este usuário?');">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>