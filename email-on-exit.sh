#!/bin/bash
# assumes postfix or similar is configured and working

if [ $# -ne 1 ] || [ ! $1 -gt 0 ]; then
    echo "Usage: $0 <PID>"
    exit 1
fi

# Store the PID of the process passed as an argument
pid=$1

# Function to send an email
function send_email() {
    local recipient_email="admin@localhost.test"
    local subject="Process $pid has exited with status $exit_status"
    local body="This email was generated on $(date '+%x, %X'). I hope it finds you well and may your future exit statuses be 0."

    echo "$body" | mail -s "$subject" "$recipient_email"
}

# Wait for the process to exit and capture its exit status
wait $pid
exit_status=$?

# Once the process has exited, send the email alert with the exit status
send_email || {
    echo "FAILED to send email!"
    exit 1
}
