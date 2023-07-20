function showNotification(message) {
  // Check if the Notification API is supported
  if ('Notification' in window) {
    // Check the permission status
    if (Notification.permission === 'granted') {
      // Create and show the notification
      new Notification('Process Watcher', {
        body: message,
        icon: 'path/to/notification-icon.png' // Replace with the path to your notification icon
      });
    } else if (Notification.permission !== 'denied') {
      // Request permission from the user
      Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
          // Create and show the notification
          new Notification('Process Watcher', {
            body: message,
            icon: 'path/to/notification-icon.png' // Replace with the path to your notification icon
          });
        }
      });
    }
  }
}

function watchProcess(pid, processName) {
  const watchButton = document.getElementById('watch_' + pid);
  watchButton.disabled = true;
  watchButton.innerHTML = 'Watching...';

  const xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);

        if (response === true) {
          // Process is still running, check again after 2 seconds
          setTimeout(() => watchProcess(pid, processName), 2000);
        } else {
          alert(processName + ' has finished running');
          watchButton.innerHTML = 'Finished';
          watchButton.disabled = true;

          // Change the color of the table cell corresponding to the watched process
          const processRow = document.getElementById('row_' + pid);
          processRow.style.backgroundColor = '#f2f2f2'; // Change to your desired color

          // Remove the process from the table
          const processTable = processRow.parentElement;
          processTable.removeChild(processRow);

          // Remove the process from the watched processes cookie
          const watchedProcesses = getWatchedProcesses();
          delete watchedProcesses[pid];
          setWatchedProcesses(watchedProcesses);
        }
      } else {
        alert('Failed to watch process ' + pid);
        watchButton.innerHTML = 'Watch';
        watchButton.disabled = false;
      }
    }
  };

  // Store the process name in the cookie
  const watchedProcesses = getWatchedProcesses();
  watchedProcesses[pid] = processName;
  setWatchedProcesses(watchedProcesses);

  xhr.open('GET', 'watch_process.php?pid=' + pid, true);
  xhr.send();
}

function getCookie(name) {
  const value = '; ' + document.cookie;
  const parts = value.split('; ' + name + '=');
  if (parts.length === 2) return parts.pop().split(';').shift();
}

function getWatchedProcesses() {
  const watchedProcessesCookie = getCookie('watched_processes');
  return watchedProcessesCookie ? JSON.parse(watchedProcessesCookie) : {};
}

function setWatchedProcesses(watchedProcesses) {
  const cookieString = Object.keys(watchedProcesses).map((pid) => {
    const processName = watchedProcesses[pid];
    return `${pid}=${encodeURIComponent(processName)}`;
  }).join('; ');

  document.cookie = cookieString + '; path=/';
}

function filterTableRows() {
  const filterInput = document.getElementById('filter');
  const filterValue = filterInput.value.trim().toLowerCase();

  const tableRows = document.querySelectorAll('table tr:not(:first-child)');

  tableRows.forEach((row) => {
    const processNameCell = row.querySelector('td:first-child');
    const processName = processNameCell.textContent.trim().toLowerCase();

    if (processName.includes(filterValue)) {
      row.style.display = 'table-row';
    } else {
      row.style.display = 'none';
    }
  });
}

function showEmailForm(pid, processName) {
  const emailForm = document.getElementById('emailForm');
  emailForm.style.display = 'block';

  const emailFormSubmit = document.getElementById('emailNotificationForm');
  emailFormSubmit.onsubmit = function (event) {
    event.preventDefault();
    const recipientEmail = document.getElementById('recipientEmail').value;
    const attachmentPath = document.getElementById('attachmentPath').value;

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          const response = xhr.responseText;
          alert(response); // You can display the response message here
          emailForm.style.display = 'none'; // Hide the email form after submitting
        } else {
          alert('Failed to send email.');
        }
      }
    };

    xhr.open('POST', 'email.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(
      `recipientEmail=${encodeURIComponent(recipientEmail)}&attachmentPath=${encodeURIComponent(attachmentPath)}&pid=${pid}&processName=${encodeURIComponent(processName)}`
    );
  };
}
