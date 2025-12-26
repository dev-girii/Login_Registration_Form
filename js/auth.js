/* Simple client-side auth helper
   Uses localStorage key 'isLoggedIn' (string 'true') to indicate an authenticated session.
   This is a client-only shim for demo purposes. Replace with server-side session logic later.
*/
function isLoggedIn() {
  try {
    return localStorage.getItem('isLoggedIn') === 'true';
  } catch (e) {
    return false;
  }
}

function requireAuth(redirectTo) {
  if (!isLoggedIn()) {
    // give a tiny delay to allow page to render briefly if desired
    window.location.replace(redirectTo);
  }
}

// helper to mark user as logged in (for quick testing)
function markLoggedIn(state) {
  try { localStorage.setItem('isLoggedIn', state ? 'true' : 'false'); } catch (e) {}
}

function logout(redirectTo){
  try{
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('userId');
    localStorage.removeItem('user');
  }catch(e){}
  // navigate away to the provided URL or homepage
  window.location.replace(redirectTo || 'index.html');
}

// export to global for console access
window.auth = { isLoggedIn, requireAuth, markLoggedIn, logout };
