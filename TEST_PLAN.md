# Test Plan - Voter Side (Tridha Bhandhari)

Project: Online Voting System | BCA 5th Semester, 2026 | Bhawanipur Global Education
Setup: XAMPP running, database `online_voting` imported, `sample_data.sql` imported.

| No | FR | Page | Test steps | Expected result | Status |
|----|----|------|-----------|-----------------|--------|
| 1 | FR-1 | register.php | Enter name, new email, password (6+ chars), same confirm password, click Register | "Registration successful" message; voter saved in `voters` table | Pass / Fail |
| 2 | FR-2 | register.php | Register again with the same email | "This email is already registered" | Pass / Fail |
| 3 | FR-3 | phpMyAdmin | Open `voters` table, look at the password column | Password is a long hash (starts with `$2y$`), not plain text | Pass / Fail |
| 4 | FR-1 | register.php | Leave fields empty / enter wrong email / short password / different confirm password | Matching error message is shown, nothing is saved | Pass / Fail |
| 5 | FR-4 | login.php | Log in with correct email and password | Redirected to dashboard, name shown in menu | Pass / Fail |
| 6 | FR-5 | login.php | Log in with wrong password | "Invalid email or password" | Pass / Fail |
| 7 | FR-5 | login.php | Log in with an email that is not registered | "Invalid email or password" | Pass / Fail |
| 8 | FR-6 | logout.php | Click "Log out" | Redirected to login page | Pass / Fail |
| 9 | FR-6 | dashboard.php | After logout, open `/dashboard.php` directly | Redirected to login page | Pass / Fail |
| 10 | FR-14 | dashboard.php | Log in while one election is active | Active election is listed with "Vote now" button | Pass / Fail |
| 11 | FR-14 | dashboard.php | Change election dates to the past in phpMyAdmin | Election is not listed; "no active elections" message | Pass / Fail |
| 12 | FR-15 | vote.php | Click "Vote now" | Election title and all its candidates are shown | Pass / Fail |
| 13 | FR-16 | vote.php | Select one candidate, click "Submit my vote" | Vote is saved in `votes` table | Pass / Fail |
| 14 | FR-16 | vote.php | Click submit without selecting a candidate | Browser asks to select one option | Pass / Fail |
| 15 | FR-17 | vote.php | Open `vote.php?election_id=1` again after voting | "You have already voted in this election" | Pass / Fail |
| 16 | FR-17 | dashboard.php | Open dashboard after voting | "You have voted" label instead of the button | Pass / Fail |
| 17 | FR-18 | vote.php | Submit a valid vote | "Your vote has been recorded. Thank you for voting." | Pass / Fail |
| 18 | FR-19 | pages / database | Check all voter pages | No page shows which candidate the voter chose | Pass / Fail |
| 19 | FR-20 | vote.php | Set election start time in the future, open `vote.php?election_id=1` | "This election has not started yet" | Pass / Fail |
| 20 | FR-20 | vote.php | Set election end time in the past, open `vote.php?election_id=1` | "This election has ended" | Pass / Fail |
| 21 | Security | vote.php | Open `vote.php?election_id=1` without logging in | Redirected to login page | Pass / Fail |
| 22 | Security | register.php | Enter `<b>test</b>` as name | Shown as plain text, not bold (htmlspecialchars) | Pass / Fail |

Note: FR-7 to FR-13 (admin functions) are tested by Pritam Mondal.
