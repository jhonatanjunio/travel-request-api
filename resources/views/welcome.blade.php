<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bem-vindo</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Space Grotesk', sans-serif;
                background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
                color: #ffffff;
                line-height: 1.6;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.7' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
                opacity: 0.02;
                pointer-events: none;
            }

            .container {
                flex: 1;
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 4rem;
            }

            .logo-wrapper {
                position: relative;
                width: 400px;
                height: auto;
            }

            .logo {
                width: 100%;
                height: auto;
                filter: drop-shadow(0 0 30px rgba(255, 255, 255, 0.15))
                        drop-shadow(0 0 15px rgba(255, 0, 0, 0.25));
                animation: subtle-float 6s ease-in-out infinite;
                transition: filter 0.3s ease;
            }

            .logo:hover {
                filter: drop-shadow(0 0 40px rgba(255, 255, 255, 0.2))
                        drop-shadow(0 0 20px rgba(255, 0, 0, 0.35));
            }

            @keyframes subtle-float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }

            .content {
                text-align: center;
                max-width: 600px;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 1rem;
                font-weight: 500;
                color: #9e9e9e;
            }

            p {
                font-size: 1.1rem;
                color: #757575;
                margin-bottom: 2rem;
            }

            .repo-link {
                display: inline-block;
                background-color: #ff0000;
                color: white;
                text-decoration: none;
                padding: 1rem 2rem;
                border-radius: 8px;
                font-weight: 500;
                border: 1px solid transparent;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .repo-link:hover {
                background-color: #cc0000;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(255, 0, 0, 0.2);
                border-color: rgba(255, 0, 0, 0.3);
                letter-spacing: 0.5px;
            }

            footer {
                text-align: center;
                padding: 2rem;
                color: #757575;
                font-size: 0.9rem;
                border-top: 1px solid rgba(255, 255, 255, 0.05);
                margin-top: auto;
            }

            @media (max-width: 768px) {
                .container {
                    padding: 2rem 1rem;
                }

                .logo-wrapper {
                    width: 280px;
                }

                h1 {
                    font-size: 1.75rem;
                }
            }

            html {
                scroll-behavior: smooth;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo-wrapper">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="logo">
            </div>
            
            <div class="content">
                <h1>Bem-vindo ao Projeto</h1>
                <p>Este é um projeto desenvolvido com Laravel. Para mais informações sobre como instalar e configurar, acesse o repositório no GitHub.</p>
                
                <a href="https://github.com/jhonatanjunio/travel-request-api" class="repo-link">
                    Ver no GitHub
                </a>
            </div>
        </div>

        <footer>
            &copy; {{ date('Y') }} - Todos os direitos reservados
        </footer>
    </body>
</html>