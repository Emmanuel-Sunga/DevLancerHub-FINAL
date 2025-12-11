document.addEventListener('DOMContentLoaded', function () {
  const roleSelect = document.getElementById('role');
  const employeeFields = document.getElementById('employeeFields');
  const skillsField = document.getElementById('skills');
  const experienceField = document.getElementById('experience_years');

  roleSelect.addEventListener('change', function () {
    if (this.value === 'employee') {
      employeeFields.style.display = 'block';
      skillsField.setAttribute('required', 'required');
      experienceField.setAttribute('required', 'required');
    } else {
      employeeFields.style.display = 'none';
      skillsField.removeAttribute('required');
      experienceField.removeAttribute('required');
    }
  });

  if (roleSelect.value === 'employee') {
    employeeFields.style.display = 'block';
  }
});
