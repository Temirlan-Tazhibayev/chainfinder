<!DOCTYPE html>
<html lang="en">
<head>
    <title>Web App with Bootstrap</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/all.min.css"> <!-- AJAX FONT-AWESOME 5.13.3 -->

    <script src="/js/jquery.min.js"></script>
    <script src="/js/popper.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>

</head>
<body>

<!-- Navbar -->
<? require_once WWW_PATH . '/system/view/navbar.php'; ?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Log In</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- <div class="d-flex justify-content-center"> -->
                <!-- <button type="button" class="btn btn-outline-dark mr-2"><i class="fab fa-google mr-2"></i>Google</button> -->
                <!-- <button type="button" class="btn btn-outline-dark"><i class="fab fa-facebook-f mr-2"></i>Facebook</button> -->
                <!-- </div> -->
                <!-- <p>-----------------------------------or-----------------------------------</p> -->
                <form>
                    <div class="form-group">
                        <label for="email">Email address:</label>
                        <input type="email" class="form-control" id="email">
                    </div>
                    <div class="form-group">
                        <label for="pwd">Password:</label>
                        <input type="password" class="form-control" id="pwd">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Log In</button>
                    </div>
                </form>
                <hr>
                <div class="text-center">
                    <a href="#" class="btn btn-link">Forgot Password?</a>
                    <a href="#" class="btn btn-link">Register an Account</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Error 404: Page Not Found
                </div>
                <div class="card-body">
                    <p>The page you requested could not be found. Please check the URL or try searching for the page using the search bar below:</p>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>