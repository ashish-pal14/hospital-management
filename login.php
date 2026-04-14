<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; }
        .login-card { max-width: 400px; margin-top: 100px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .btn-login { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4">Hospital Management System</h3>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" required value="admin@hospital.com">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" required value="password">
                            </div>
                            <button type="submit" class="btn btn-primary btn-login w-100">Login</button>
                        </form>
                        <div id="errorMsg" class="alert alert-danger mt-3 d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            try {
                const response = await fetch('/api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    const errorDiv = document.getElementById('errorMsg');
                    errorDiv.textContent = data.error || 'Login failed';
                    errorDiv.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
