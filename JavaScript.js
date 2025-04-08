
//Password Visibility

function togglePasswordVisibility(icon) {
  const targetId = icon.getAttribute('data-target');
  const passwordField = document.getElementById(targetId);

  if (passwordField.type === 'password') {
      passwordField.type = 'text';
      icon.classList.add('ri-eye-off-line'); 
      icon.classList.remove('ri-eye-line');
  } else {
      passwordField.type = 'password';
      icon.classList.add('ri-eye-line');
      icon.classList.remove('ri-eye-off-line');
  }
}


window.addEventListener('load', () => {
  const welcomeSection = document.querySelector('.welcome-section');
  
  setTimeout(() => {
      welcomeSection.classList.add('show');
  }, 500); 
});


const gallery = document.getElementById('gallery');
const scrollAmount = 520;

function scrollGallery(direction) {
    gallery.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
}




// Function to toggle profile options visibility
function toggleProfileOptions(event) {
  let profileOptions = document.getElementById("profileOptions");

  if (profileOptions.style.display === "block") {
    profileOptions.style.display = "none";
  } else {
    profileOptions.style.display = "block";
  }


  event.stopPropagation();
}

document.addEventListener("click", function(event) {
  let profileOptions = document.getElementById("profileOptions");
  let profileSection = document.querySelector(".profile-section");

  if (!profileSection.contains(event.target)) {
    profileOptions.style.display = "none";
  }
});



function showEditForm(id) {
  document.getElementById('editForm_' + id).style.display = 'block';
}

function hideEditForm(id) {
  document.getElementById('editForm_' + id).style.display = 'none';
}


//Calendar
document.addEventListener('DOMContentLoaded', function () {
  let calendarEl = document.getElementById('calendar');
  let calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      selectable: true,
      dateClick: function (info) {
          document.getElementById('selectedDate').textContent = `Selected Date: ${info.dateStr}`;
          document.getElementById('bookingModal').classList.add('active');
          window.selectedDate = info.dateStr;
      }
  });
  calendar.render();
  window.calendar = calendar;
});








