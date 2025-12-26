/* app.js
   Uses jQuery AJAX for all backend interactions (no form submissions).
   Stores minimal session in localStorage (isLoggedIn, userId, user data).
*/
$(function(){
  // REGISTER
  $('#registerForm').on('submit', function(e){
    e.preventDefault();
    var $f = $(this);
    var data = {
      fullname: $f.find('[name="fullname"]').val(),
      username: $f.find('[name="username"]').val(),
      email: $f.find('[name="email"]').val(),
      password: $f.find('[name="password"]').val(),
      confirm_password: $f.find('[name="confirm_password"]').val(),
      age: $f.find('[name="age"]').val(),
      dob: $f.find('[name="dob"]').val(),
      contact: $f.find('[name="contact"]').val(),
      address: $f.find('[name="address"]').val()
    };

    if(!data.fullname || !data.username || !data.email || !data.password) {
      alert('Please fill required fields');
      return;
    }
    if(data.password !== data.confirm_password) { alert('Passwords do not match'); return; }

    $.ajax({
      url: 'php/register.php',
      type: 'POST',
      contentType: 'application/json; charset=utf-8',
      data: JSON.stringify(data),
      dataType: 'json'
    }).done(function(resp){
      if(resp && resp.success){
        alert('Registration successful. Please log in.');
        window.location.href = 'login.html';
      } else {
        alert(resp.message || 'Registration failed');
      }
    }).fail(function(){ alert('Network or server error during registration'); });
  });

  // LOGIN
  $('#loginForm').on('submit', function(e){
    e.preventDefault();
    var $f = $(this);
    var data = {
      identifier: $f.find('[name="identifier"]').val(),
      password: $f.find('[name="password"]').val()
    };
    if(!data.identifier || !data.password){ alert('Please fill required fields'); return; }

    $.ajax({
      url: 'php/login.php',
      type: 'POST',
      contentType: 'application/json; charset=utf-8',
      data: JSON.stringify(data),
      dataType: 'json'
    }).done(function(resp){
      if(resp && resp.success && resp.user){
        localStorage.setItem('isLoggedIn','true');
        localStorage.setItem('userId', resp.user.id);
        localStorage.setItem('user', JSON.stringify(resp.user));
        window.location.href = 'profile.html';
      } else {
        alert(resp.message || 'Login failed');
      }
    }).fail(function(){ alert('Network or server error during login'); });
  });

  // PROFILE page: display-only
  if($('#profileCard').length || $('#notAuthed').length){
  var userId = localStorage.getItem('userId');
  var authed = (typeof auth !== 'undefined') ? auth.isLoggedIn() : (localStorage.getItem('isLoggedIn') === 'true');
  var localUserRaw = localStorage.getItem('user');
  var localUser = null;
  try { localUser = localUserRaw ? JSON.parse(localUserRaw) : null; } catch(e){ localUser = null; }

    function showNotAuth(){
      $('#notAuthed').removeClass('d-none');
      $('#profileCard').addClass('d-none');
      $('#avatar').text('--');
      $('#displayFullname').text('Guest');
      $('#displayUsername').text('@guest');
    }

    function showProfile(u, p){
      $('#notAuthed').addClass('d-none');
      $('#profileCard').removeClass('d-none');

      var fullname = u.fullname || '';
      var username = u.username || '';
      $('#displayFullname').text(fullname || '');
      $('#displayUsername').text(username ? ('@' + username) : '');
      $('#fullnameDisplay').text(fullname || '-');
      $('#usernameDisplay').text(username || '-');
      $('#emailDisplay').text(u.email || '-');
      $('#ageDisplay').text(p && p.age ? p.age : '-');
      $('#dobDisplay').text(p && p.dob ? p.dob : '-');
      $('#contactDisplay').text(p && p.contact ? p.contact : '-');
      $('#addressDisplay').text(p && p.address ? p.address : '-');

      // avatar initials
      var initials = '--';
      if(fullname){
        initials = fullname.split(' ').map(function(s){ return s.charAt(0); }).slice(0,2).join('').toUpperCase();
      } else if(username){
        initials = username.substring(0,2).toUpperCase();
      }
      $('#avatar').text(initials);
    }

    if(!authed){
      showNotAuth();
    } else {
      // Prefer server data when we have a userId; otherwise fall back to localStorage user
      if(userId){
        $.getJSON('php/profile_get.php', { userId: userId }).done(function(resp){
          if(resp && resp.success){
            var u = resp.user || {};
            var p = resp.profile || {};
            showProfile(u, p);
          } else {
            console.warn('Failed to load profile from server', resp && resp.message);
            if(localUser) showProfile(localUser, {}); else showNotAuth();
          }
        }).fail(function(){
          console.warn('Failed to fetch profile from server');
          if(localUser) showProfile(localUser, {}); else showNotAuth();
        });
      } else if(localUser){
        // no userId, but have local data from login: use that
        showProfile(localUser, {});
      } else {
        showNotAuth();
      }
    }
  }
});
