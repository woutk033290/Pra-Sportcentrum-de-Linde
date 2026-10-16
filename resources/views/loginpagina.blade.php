<div class="login-container">
    <div class="login-card">

        <h1 class="login-title">
            Inloggen
        </h1>

        @if(session('error'))
            <div class="login-error">
                {{ session('error') }}
            </div>
        @endif

        {{ route('login.verwerken') }}
            @csrf

            <div class="login-group">
                <label for="username" class="login-label">
                    Gebruikersnaam
                </label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="login-input"
                    required
                >
            </div>

            <div class="mb-6">
                <label for="password" class="login-label">
                    Wachtwoord
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="login-input"
                    required
                >
            </div>

            <button type="submit" class="login-button">
                Inloggen
            </button>
        </form>

    </div