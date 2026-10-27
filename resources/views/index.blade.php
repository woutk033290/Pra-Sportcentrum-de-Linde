<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    <main class="pagina">

        <section class="hero">
            <h1>Sportschool De Linde</h1>
            <p>Groepslessen voor iedereen: van spinning en yoga tot bootcamp en aquagym.</p>
            <a href="#lessen" class="btn btn-licht">Bekijk de lessen</a>
        </section>

        <h2 class="sectie-titel" id="lessen">Onze lessen</h2>

        <section class="lessen-grid">
            <article class="les-kaart">
                <div class="les-afbeelding" role="img" aria-label="Spinning"></div>
                <div class="les-inhoud">
                    <h3>Spinning</h3>
                    <p>Intensieve fietsles voor volwassenen.</p>
                    <a href="#" class="btn">Meer info</a>
                </div>
            </article>

            <article class="les-kaart">
                <div class="les-afbeelding" role="img" aria-label="Yoga"></div>
                <div class="les-inhoud">
                    <h3>Yoga</h3>
                    <p>Rustige stretch- en ademhalingsles voor iedereen.</p>
                    <a href="#" class="btn">Meer info</a>
                </div>
            </article>

            <article class="les-kaart">
                <div class="les-afbeelding" role="img" aria-label="Bootcamp"></div>
                <div class="les-inhoud">
                    <h3>Bootcamp</h3>
                    <p>Krachttraining buiten voor gevorderden.</p>
                    <a href="#" class="btn">Meer info</a>
                </div>
            </article>

            <article class="les-kaart">
                <div class="les-afbeelding" role="img" aria-label="Aquagym"></div>
                <div class="les-inhoud">
                    <h3>Aquagym</h3>
                    <p>Bewegen in het water, geschikt voor beginners.</p>
                    <a href="#" class="btn">Meer info</a>
                </div>
            </article>
        </section>

    </main>

    @include('partials.footer')
</body>
</html>