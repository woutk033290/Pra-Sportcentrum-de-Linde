
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Company - Home</title>

    @vite(['resources/css/app.css'])
</head>
<body>
    <section class="page">

        <!-- Header -->
        <header class="el-header full">
            <strong class="company-name">Company</strong>

            <nav>
                <a class="inlog" href="/login">Log in</a>
            </nav>
        </header>

        <!-- Images -->
        <div class="row images-row" id="features">
            <figure class="el-image">
                <div class="imgph" role="img" aria-label="Image placeholder 1"></div>
            </figure>

            <figure class="el-image">
                <div class="imgph" role="img" aria-label="Image placeholder 2"></div>
            </figure>

            <figure class="el-image">
                <div class="imgph" role="img" aria-label="Image placeholder 3"></div>
            </figure>

            <figure class="el-image">
                <div class="imgph" role="img" aria-label="Image placeholder 4"></div>
            </figure>
        </div>

        <!-- Text -->
        <div class="row text-row">
            <div class="el-paragraph">
                <h2>Feature One</h2>
                <p>Body text goes here. Replace this with your own copy.</p>
            </div>

            <div class="el-paragraph">
                <h2>Feature Two</h2>
                <p>Body text goes here. Replace this with your own copy.</p>
            </div>

            <div class="el-paragraph">
                <h2>Feature Three</h2>
                <p>Body text goes here. Replace this with your own copy.</p>
            </div>

            <div class="el-paragraph">
                <h2>Feature Four</h2>
                <p>Body text goes here. Replace this with your own copy.</p>
            </div>
        </div>

        <!-- Buttons -->
        <div class="row buttons-row" id="pricing">
            <div class="el-button">
                <button type="button">Learn More</button>
            </div>

            <div class="el-button">
                <button type="button">Learn More</button>
            </div>

            <div class="el-button">
                <button type="button">Learn More</button>
            </div>

            <div class="el-button">
                <button type="button">Learn More</button>
            </div>
        </div>



        <!-- Footer -->
        <footer class="el-footer full" id="contact">
            <p>&copy; Company</p>

            <nav>
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">Contact</a>
            </nav>
        </footer>

    </section>
</body>
</html>
