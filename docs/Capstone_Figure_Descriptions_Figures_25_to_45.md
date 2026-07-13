# Figure Descriptions for the School Violation System (VioTrack)
## Objectives 1, 2, and 3

**Prepared for Capstone Documentation**

This section shows the pictures (Figures 25 to 45) of the main screens in the School Violation System. Each picture has a simple explanation below it. The words used here are easy to understand. Every description tells what the user can see on the screen, what buttons to click, and what happens after they use it. All descriptions are based on the real labels and buttons shown in the system.

---

## Objective 1: Recording, Storing, and Retrieving Student Violation Records

### Figure 25. Log a Violation

Figure 25 shows the **Log a Violation** screen of the School Violation System. This screen is used to record a new student violation in the system. When the admin opens this page, they will see a big title that says **Log a Violation** and a short note that says **Record a new incident and attach necessary evidence.** At the top of the form, there are three steps shown in circles: **Student**, **Violation**, and **Details**.

In **Step 1 (Student)**, the admin types in the search box to find the student. The placeholder says **Search by name, ID, or department...** Each student in the list shows their full name, student number, and department. The admin clicks on the correct student to select them. A check mark appears on the selected student. Then the admin clicks the **Next** button to go to Step 2.

In **Step 2 (Violation)**, the heading says **Select Violation**. The admin can search using the box that says **Search violation code or title...** Each violation shows a color dot for severity (Minor, Major, or Critical), the violation code, and the violation title. The admin clicks the correct violation, then clicks **Next** again.

In **Step 3 (Details)**, the admin fills in the **Date & Time of Incident**, the **Witness** name (if there is one), and the **Incident Description**. The screen also shows a summary of who the offender is and what offense was picked. When everything is complete, the admin clicks **Submit Violation**. The system saves the new case in the database with a **Pending** status. The case can now be seen in the **Violation Cases** list and can be updated later.

This screen supports **Objective 1** because it helps the school record violations in a proper and organized way.

---

### Figure 26. Auto Sanction Preview

Figure 26 shows the **Auto Sanction Preview** inside Step 3 of the **Log a Violation** screen. After the admin picks a student and a violation, the system automatically checks how many times that student already did the same violation before. A red or rose-colored box appears on the screen. It shows the words **Expected Sanction (Offense #1)**, **Offense #2**, or higher, depending on how many times the student offended.

Below that label, the system shows the exact sanction text from the school rules. Examples include verbal warning, written warning, community service, or referral to Student Affairs. The admin can read this before clicking **Submit Violation**. They do not need to compute the punishment by hand.

This feature supports **Objective 1** because it makes sanction assignment fair, consistent, and based on the student’s violation history stored in the system.

---

### Figure 27. All Cases List

Figure 27 shows the **Violation Cases** page, also called the All Cases List. At the top, the page title says **Violation Cases** with a note: **Manage and track student violation records, hearings, and sanctions.** On the right side, there is a blue button labeled **Record Violation** that opens the Log a Violation screen.

Below the header, four summary boxes show numbers:
- **Total Cases**
- **Pending**
- **Hearing Scheduled**
- **Closed Cases**

These numbers change based on what is saved in the system. Under the summary boxes is a search bar with the text **Search by student name or violation...** There are also filter drop-downs for **Status** (All Statuses, Pending, Endorsed, Hearing Scheduled, Closed), **Severity** (All Severities, Minor, Major), **Department**, **Academic Year**, and date fields labeled **From** and **To**.

The main part of the screen is a table with columns:
- **Date / Status**
- **Student** (name, department, and section)
- **Violation Details** (violation title and severity)
- **Actions** (View, Edit, and Delete icons)

Each row is one violation case. When a new case is saved from Log a Violation, it appears here automatically. The admin uses this page every day to check open cases, find old cases, and open case details.

This screen supports **Objective 1** because it stores and displays all violation records in one place.

---

### Figure 28. Case Details

Figure 28 shows the **Case Details** screen for one violation case. The page title shows **Violation Case #0001** (the number changes per case). Below it says **Student Respondent:** followed by the student’s name. At the top right, there are **Edit** and **Print** buttons.

A progress bar in the middle shows where the case is in the process:
- **Pending**
- **Hearing Scheduled**
- **Hearing**
- **Closed**

If the case was sent to the grievance committee, the screen shows **Endorsed to Grievance Committee** instead of the normal progress bar.

The main card shows the violation title, category, violation code, and the current status badge (color-coded). Below that is the full **Incident Description** written by the admin. It also shows **Date & Time** of the incident and **Witnesses** (or **No Witness Logged** if empty). There is an **Assigned Sanction** section that shows the offense level and sanction given.

On the side, the screen shows the student’s name, department, section, and year level. Other sections include **Attached Evidence**, **Hearing Sessions**, and **Case Actions** such as OSA intervention. Buttons at the bottom let the admin **Start Hearing**, **Complete Hearing**, **Endorse to Grievance**, **Close Case**, or **Record OSA Intervention**, depending on the case status.

This screen supports **Objective 1** because it lets the admin view the full record of one violation case anytime.

---

### Figure 29. Violation Catalog

Figure 29 shows the **Rules & Regulations** page, which is the Violation Catalog of the system. The page header says **Rules & Regulations** with a note: **Manage violation guidelines, categorize offenses, and establish standardized violation severity classifications.** There is a button labeled **Add Rule Category** for adding new rules.

There is a search box with the text **Search rules, keywords, or codes...** and a **Category** drop-down to filter by type. The table lists every violation rule with these columns:
- **Violation Rule / Code** (shows the code and full title)
- **Category Class**
- **Severity Level** (Minor Severity or Major Severity with color badges)
- **Actions** (Edit and Delete)

Each rule in this list is what the admin picks when logging a violation. When the school adds or changes a rule here, it right away affects new cases recorded through **Log a Violation**. This keeps all violation records clean, standard, and easy to compare in reports.

This screen supports **Objective 1** because it holds the official list of violations used when recording cases.

---

### Figure 30. Student Import

Figure 30 shows the **Import Students** page. The title says **Import Students** and below it says **Upload a CSV or Excel file to bulk import students.** The user sees a big upload area where they can **Click to upload** or **drag and drop** a file. The allowed file types are **XLSX, XLS or CSV (max 10 MB)**.

There is also a **Download Template** link so the admin can get the correct column headers before preparing the file. After the user selects a file and uploads it, a progress bar shows how much of the file has been uploaded. When done, the system reads each row and saves the students into the database.

If some rows have errors, the system shows which rows failed. After a successful import, those students appear in the **Students** list and can be searched when logging a violation. This saves time compared to typing each student one by one.

This screen supports **Objective 1** because it helps store student records that are needed before violations can be recorded.

---

### Figure 31. Record Retrieval

Figure 31 shows the **Record Retrieval** page under Reports. The page title is **Search Records** and the description says **Search through all past records with advanced, multi-parameter filters.** At the top is a search box for **Search by student name or ID...**

Below are filters for:
- **Start Date** and **End Date**
- **Month**
- **Violation Type** (pick from the violation list)
- **Department**
- **Academic Year**
- **Severity** (Minor or Major)

The page also shows how many **Records Found** match the search. Each result row has **View** and **Print** buttons. There is a **Clear Filters** button to reset everything. The admin can also use **Save Preset** and **Load Preset** to remember a favorite filter setup.

This page is used when the admin needs to find old records fast. The system searches the database and shows only the records that match the filters.

This screen supports **Objective 1** because it makes stored violation records easy to find and retrieve.

---

## Objective 2: Improving Case Management and Monitoring

### Figure 32. Dashboard Overview

Figure 32 shows the main **Dashboard** screen that appears after the admin logs in. At the top, four big cards show important numbers:
- **Total Students** (how many students are in the system)
- **Violation Cases** (total cases recorded)
- **Active Cases** (cases that are not closed yet)
- **Hearings** (hearings scheduled this month)

Each card can be clicked to go to the related page. Below the cards, there are charts and graphs. One chart shows **Violation Trends** per month. Another shows cases per department. There is also a chart for cases by severity (Minor vs Major).

A list of **Recent Cases** shows the latest violations with student name, violation title, date, and status badge. A **Top Violations** section lists the most common offenses. The admin can also filter by **Academic Year** to see data for a specific school year.

This dashboard gives a quick picture of what is happening in the school without opening each case one by one. It helps the OSA or dean see problems early.

This screen supports **Objective 2** because it helps the admin monitor cases and school discipline activity at a glance.

---

### Figure 33. Record Violation Step 2: Select Violation

Figure 33 shows **Step 2: Violation** inside the **Log a Violation** process. The step indicator at the top shows that Step 1 (Student) is done and Step 2 (Violation) is now active. The heading says **Select Violation** with a search box that says **Search violation code or title...**

The admin types here to find the right offense quickly. The list below shows every violation from the **Rules & Regulations** catalog. Each item displays the violation code and title, a colored dot (yellow for Minor, orange for Major, red for Critical), and a severity badge on the right.

The admin clicks the correct violation to highlight it, then clicks **Next** to go to Step 3. This step is important because picking the wrong violation will give the wrong sanction and wrong report. By selecting from the official list, the admin makes sure the case is recorded under the correct school rule.

This screen supports **Objective 2** because it guides the admin through a clear step-by-step recording process.

---

### Figure 34. Edit Case

Figure 34 shows the **Edit Case** screen. The page title says **Edit Case #** followed by the case number. A note below says **Update incident details. Status changes via hearings and case actions.** This means the admin can fix the written details here, but changing the case status (like Pending to Closed) is done through hearings and action buttons on the Case Details page.

The form shows read-only info about the student name and violation title. The editable fields are:
- **Date & Time of Incident**
- **Witness**
- **Incident Description**

At the bottom, there is a **Save Changes** button and a red **Move to Trash** button. When saved, the updated information is stored in the database and shows on the Case Details page. Closed cases cannot be edited from this screen.

This screen supports **Objective 2** because it lets the admin update case records when corrections are needed.

---

### Figure 35. Student Profile with Violations

Figure 35 shows the **Student Profile** page for one student. The header shows the student’s full name, department, year level, and section. Buttons at the top include:
- **Print**
- **Edit**
- **Message Guardian**
- **Log Violation**

A **Risk Level** badge shows if the student is **Low**, **Medium**, or **High** risk based on how many violations they have. The **Student Information** section shows email and guardian contact details. The **Incident Summary** section shows counts for **Total**, **Minor**, and **Major** violations.

Below that is a **Violation Timeline** table listing all cases linked to this student. Each row shows the date, violation code, violation name, severity, status (Pending, Hearing Scheduled, Closed, or Endorsed to Grievance), and a **View** button to open the full case.

This page lets the admin see the whole behavior record of one student in one place. It is useful during counseling, parent meetings, or when deciding if a repeat offender needs a hearing.

This screen supports **Objective 2** because it helps the admin monitor one student’s full violation history.

---

### Figure 36. Cases Search and Filter

Figure 36 shows the **Search and Filter** section on the **Violation Cases** page. The search bar lets the admin type a student name or violation name. As they type, the list below updates after a short delay without reloading the whole page.

The filter drop-downs include:
- **Status**: All Statuses, Pending, Endorsed, Hearing Scheduled, Closed
- **Severity**: All Severities, Minor, Major
- **Department**: all school departments
- **Academic Year**
- **From** and **To** date pickers

A clear button resets all filters back to show every case. When filters are active, only matching rows stay in the table. For example, if the admin picks Status = Pending and a specific Department, only open cases from that department will show.

This screen supports **Objective 2** because it makes case monitoring faster and more organized.

---

### Figure 37. Students Search

Figure 37 shows the **Students** list page with its search and filter tools. The search box lets the admin type a student’s name, student number, or email. Filters include **Department**, **Year Level**, and **Academic Year**.

Buttons at the top include **Add Student**, **Import Students**, **Promote Year Levels**, and **Graduate 4th Years**. The table shows each student’s name, student number, department, year level, section, and action buttons (**View**, **Edit**, **Delete**).

When the admin searches, only students matching the typed words appear. This page is the starting point before opening a student profile or logging a violation. Instead of scrolling through many names, the admin types a few letters and finds the student quickly.

This screen supports **Objective 2** because it helps the admin find student records fast for case management.

---

### Figure 38. Advanced Record Retrieval

Figure 38 shows the **Advanced Record Retrieval** filters on the **Search Records** page under Reports. This is a more detailed version of basic search. The admin can combine many filters at the same time: student name or ID, **Violation Type**, **Department**, **Academic Year**, **Severity**, **Start Date**, **End Date**, or a whole **Month**.

The **Save Preset** feature lets the admin name and save a favorite filter setup in the browser. **Load Preset** brings it back with one click. Results appear in a table with student info, violation details, date, and status. The page shows how many **Records Found** match the search. Each result can be viewed or printed.

This is helpful when the school needs a list of all violations in one department, or when preparing documents for a hearing. The system reads all saved cases and shows only what matches every filter chosen.

This screen supports **Objective 2** because it helps the admin manage and review many records in a detailed way.

---

## Objective 3: Reports, Analytics, and Decision-Making

### Figure 39. System Reports and Trends

Figure 39 shows the **System Reports** page. The title says **System Reports** with a note: **Overview statistics — total cases, status breakdown & department analysis.** Five stat cards at the top show:
- **Total Cases**
- **Pending**
- **Hearing**
- **Endorsed**
- **Closed**

Below that, a line chart titled **Comparative Monthly Cases** shows monthly trends for **Minor** and **Major** violations from January to December. A donut chart titled **Status Distribution** shows the breakdown of cases by status. Another section titled **Cases by Department** shows which departments have the most cases. A **Top Violations** section lists the most recorded offenses.

The date stamp **As of [today’s date]** appears on the page. This report helps school officials answer questions like: Are violations going up or down? Which month had the most cases? Which department needs more attention?

This screen supports **Objective 3** because it turns saved case data into reports for decision-making.

---

### Figure 40. Department Top Violations

Figure 40 shows the **Department Top Violations** report on the **System Reports** or **Dashboard** page. This report groups all violation cases by the student’s department (course or college). For each department, it shows which violations happen most often and how many times each one was recorded.

For example, it might show that one department has many **Late Attendance** cases and another has more **Improper Uniform** cases. The data is shown in a chart or ranked list so the admin can quickly see which department has the highest number of cases and what the common problems are.

School officials use this to plan seminars, reminders, or stricter monitoring in departments with many violations. It also helps deans see their own department’s situation.

This screen supports **Objective 3** because it shows where violations are most common across departments.

---

### Figure 41. Sanctions Compliance

Figure 41 shows the **Sanctions Report** page. The header says **Sanctions Report** with the description: **Track imposed sanctions, compliance status, and sanction outcomes per student.** Four summary cards show:
- **Total Sanctions**
- **Sanction Served**
- **Pending Sanction**
- **Compliance Rate** (shown as a percentage)

Filters let the admin narrow results by **Department**, **Severity**, **Sanction Status** (Served or Pending), and **Date Range**. The table lists each case with student name, violation, sanction given, and whether the sanction is served or still pending.

This page answers an important question: Did the student actually complete the punishment that was given? If many sanctions are still pending, the admin knows they need to follow up. A high compliance rate means the discipline process is working well.

This screen supports **Objective 3** because it helps the school check if sanctions are being followed.

---

### Figure 42. Violation Ledger and Exports

Figure 42 shows the **Reports & Analytics** page, which works as the Violation Ledger. The page title is **Reports & Analytics** with a note about generating offense summaries and downloading reports. It lists all violation cases in a table with columns:
- **Date & Timestamp**
- **Student Profile** (name and department)
- **Violation Details** (title and category)
- **Lifecycle Status** (Pending, Hearing Scheduled, Endorsed, Closed)

At the top right, three export buttons are visible:
- **Export CSV**
- **Export PDF**
- **Print Report**

Search and filters let the admin pick a student name, department, and case status before exporting. The admin can export only the records they need, not the whole database. The exported file can be printed, emailed, or filed for school records and meetings.

This screen supports **Objective 3** because it allows official reports to be created and shared.

---

### Figure 43. Dashboard Violation Trends

Figure 43 shows the **Violation Trends** chart on the **Dashboard**. This is a line graph that shows how many violation cases were recorded each month. The horizontal line shows the months (Jan, Feb, Mar, and so on). The vertical line shows the number of cases. Each point on the line represents the total incidents in that month.

When the line goes up, it means more violations happened that month. When it goes down, fewer violations were recorded. The admin can move the mouse over each point to see the exact number. This chart updates when new cases are saved or closed.

School officials use this to spot patterns, like more violations during exam week or at the start of the school year. Instead of counting cases manually, they can see the trend in one picture on the dashboard.

This screen supports **Objective 3** because it shows violation patterns over time for better planning.

---

### Figure 44. Printable Violation Report

Figure 44 shows the **Printable Violation Report** for a single case. When the admin clicks the **Print** button on the **Case Details** page, this formal document opens in a new tab. The header shows **I-Link College of Science and Technology**, **Office of Student Affairs**, and **Official Student Violation Record**. The document title is **Violation Incident Report**.

The report is divided into sections:
- **I. Student Information** (full name, department, year and section)
- **II. Violation Details** (violation code, title, category, severity, date and time, witness, and full description)
- **III. Sanctions & Interventions** (offense level, assigned sanction, and actions taken)

A **Print Report** button at the top opens the browser print dialog. This printed copy can be signed, filed in the student’s folder, attached to a hearing packet, or given to the guardian during a meeting.

This screen supports **Objective 3** because it creates an official printed record for one violation case.

---

### Figure 45. Student Violation Summary

Figure 45 shows the **Student Violation Summary** printout from the **Student Profile** page. When the admin clicks **Print** on a student’s profile, the system generates a document titled **STUDENT VIOLATION RECORD**. The header shows **I-Link CST Violation System**, the label **Official Record**, and the date and time it was generated.

The **Student Details** section lists the student’s full name, ID or email, year and section, department, and guardian name with phone number. The **Violation History** section is a table with columns:
- **Date**
- **Code**
- **Violation**
- **Description**
- **Witness**
- **Action/Status**

Every case linked to that student appears in the table. If the student has no violations, it says **No violation records found.** This summary is used when the school needs the complete behavior history of one student in one document — for counseling, parent conference, dean’s review, or grievance committee hearing.

This screen supports **Objective 3** because it gives a complete printable summary of one student’s violation history for official use.

---

## Summary

Figures 25 to 45 show the main parts of the **School Violation System (VioTrack)** based on the three objectives of the study.

- **Figures 25 to 31** cover **Objective 1**: recording new violations step by step, saving them in the database, and finding old records through search and filters.
- **Figures 32 to 38** cover **Objective 2**: monitoring cases through the dashboard, editing case details, viewing student profiles with full violation history, and using advanced filters to manage many records.
- **Figures 39 to 45** cover **Objective 3**: generating reports, checking if sanctions were served, exporting data to CSV and PDF, reading violation trends on charts, and printing official documents for school use.

Together, these screens show that the system helps the school record violations properly, track each case from **Pending** to **Closed**, and produce reports that school officials can use to make better decisions about student discipline.
