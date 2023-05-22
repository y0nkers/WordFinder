<?php
session_start();
if (!$_SESSION['user']) {
    header('Location: index.php');
}

require_once "class/DbConnect.php";
$dbConnect = new DbConnect("user", "");
$pdo = $dbConnect->getPDO();

$stmt = $pdo->prepare("SELECT login, email FROM users WHERE login = :login");
$stmt->bindParam(':login', $_SESSION['user']['login']);
$stmt->execute();

$user = $stmt->fetch();
$_SESSION['user'] = [
    "login" => $user['login'],
    "email" => $user['email'],
];

$title = "Word Finder - личный кабинет";
require __DIR__ . '/header.php';
?>

<main class="container-fluid container-xl">
    <div class="pt-5 pb-3">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border border-dark">
                        <div class="card-header bg-dark text-white">Личный кабинет</div>
                        <div class="card-body rounded-3 field-bg">
                            <div class="row align-items-center mb-3">
                                <div class="form-group col-sm-6">
                                    <h4>Логин</h4>
                                    <p><?= $_SESSION['user']['login'] ?></p>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="form-group col-sm-6">
                                    <h4>Email</h4>
                                    <p><?= $_SESSION['user']['email'] ?></p>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="form-group col-sm-6">
                                    <a href="/core/logout.php">Выйти</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<?php require __DIR__ . '/footer.php' ?>
