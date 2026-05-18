<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Handbook;

class HandbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Policies on Attendance
        Handbook::create([
            'title' => 'Chapter I: Policies on Attendance, Absences, and Tardiness',
            'content' => '
I. Policies on Attendance, Absences, and Tardiness

General Policy Statement
Regular attendance and punctuality are essential components of academic success and personal responsibility. The institution upholds the importance of students being present and on time for all classes, activities, and academic engagements. These policies are established not only to maintain the integrity of the learning process but also to instill discipline and commitment among students.

In accordance with Section 101 of the CHED MORPHE and Section 133 of DepEd Order No. 88, s. 2010, a student who incurs absences of more than twenty percent (20%) of the prescribed number of class or laboratory periods during the school year or term shall be considered failed and will earn no credit for the course or subject. However, exemptions may be granted in cases with justifiable and reasonable grounds, subject to the approval of the appropriate authority and supported by valid documentation.

Faculty Exemptions. A faculty member may recommend an exemption for a student who exceeds the twenty percent (20%) limit for justified reasons, but such an exemption shall require the approval of the proper school authority as per institutional guidelines.

Students are reminded that while justifiable absences may be considered for exemption, they are not excused from keeping up with lessons, assignments, and examinations missed during their absence. The institution encourages proactive communication with instructors to ensure academic continuity.

The guidelines on attendance, absences, and tardiness are designed to ensure that students meet the required academic contact hours as mandated by the Commission on Higher Education (CHED). These policies outline the acceptable grounds for absences, the maximum number of allowable absences, and the consequences of excessive tardiness. Furthermore, they emphasize the need for students to communicate responsibly with faculty members regarding attendance matters.

Through these policies, i-Link CST aims to foster a culture of accountability, respect for schedules, and a commitment to educational excellence. It recognizes that consistent participation in class activities directly contributes to academic achievement and character development.

I.1. Attendance
Consistent attendance is a fundamental expectation for all students. Students must be present during all scheduled class days as stipulated in the academic calendar. Attendance is monitored from the first day of regular classes, irrespective of the student\'s actual date of enrollment. This ensures that every student receives the complete academic experience and meets the required contact hours mandated by the curriculum.
Punctuality is also emphasized as a key component of academic discipline. Students are expected to arrive on time and be prepared for class activities. Furthermore, each student monitors their attendance record to ensure compliance with institutional policies. The commitment to attendance supports academic success and instills a sense of responsibility and respect for time, which are integral to personal and professional growth.

I.2. Absences
An absence is defined as the failure of a student to attend a scheduled class session, whether for a full period or a substantial portion of it, as stipulated in the academic schedule. The following circumstances cover the “substantial portion absentee” under the definition of absence:

I.2.1. Accumulated Tardiness
A student is considered tardy when he or she arrives after the scheduled start of the class but within the first fifteen (15) minutes of the session. Three (3) instances of tardiness are equivalent to one (1) absence record. This measure is implemented to encourage punctuality and accountability among students and recognize the cumulative impact of repeated lateness on learning and class progression.

I.2.2. Late Arrival Beyond Fifteen (15) Minutes:
A student who arrives more than fifteen (15) minutes after the scheduled start of the class is considered absent for attendance purposes. However, the student is still encouraged to join the ongoing class discussions and activities to remain engaged with the learning process.

I.2.3. Leaving Class Prematurely Without a Valid Reason:
A student who leaves the classroom at least fifteen (15) minutes before the scheduled end of the class, without returning, regardless of the reason\'s validity, shall be marked as absent. This measure ensures accountability and respects the instructional hours set by the institution.

I.2.4. Non-Attendance During Official School Functions:
Students who do not attend scheduled official school activities and functions, which are replacements for regular classroom activities, shall be considered absent. Participation in these official activities is part of the academic requirements, and failure to attend will reflect on their attendance record.

I.2.5. Cutting Classes
Cutting Classes is defined as the act of being physically present within the school premises but failing to attend scheduled classes without valid permission or justification. This includes:
1. Campus Presence without Class Attendance. Students who are on campus grounds during class hours but are not present in their designated classrooms are considered to have engaged in Cutting Classes.
2. Unauthorized Departure from Class or School Functions. Students who leave class schedules or any official school activity prematurely (at least fifteen (15) minutes before the scheduled end of class or activity/official function) without prior permission from the teacher, adviser, or authorized school personnel are also deemed to have cut classes.
Cutting Classes shall be treated as an unexcused absence and will be recorded accordingly.

I.2.6. Student Suspension and Its Impact on Attendance Records
Students who are officially suspended from attending classes or participating in other official school activities as a result of sanctions for violations of the Student Handbook policies or the Student Code of Conduct and Discipline shall be considered unexcused absences for the duration of the suspension. The following stipulations apply:
1. All days missed during the suspension period will be recorded as unexcused absences and counted towards the allowable absence limit.
2. Suspensions are deemed part of the disciplinary process, and as such, missed classes or activities are not eligible for exemptions or considerations for excused absences.
3. During suspension, students remain responsible for any academic requirements, assignments, and examinations. They are also responsible for coordinating with instructors for missed lessons or activities.
The institution upholds the importance of accountability and discipline, ensuring that attendance policies are strictly enforced to maintain academic integrity and institutional standards.

I.2.7. Sanctions for Accumulated Violations of the School ID Policy
Any student who consistently violates institutional policies, including Mandatory Wearing of School ID, Unauthorized Use of Lanyards, or other related infractions, shall face progressive disciplinary measures. These accumulated violations, if reaching three (3) counts within a semester, Year, or Term, shall be considered equivalent to one (1) officially unexcused absence. For each subsequent set of three (3) violations, an additional one (1) unexcused absence shall be recorded. The institution upholds this policy to instill accountability and adherence to school regulations.
',
            'attachment' => null,
        ]);

        // 2. Policies on Identification Cards
        Handbook::create([
            'title' => 'Chapter II: Policies on Identification Cards, School Uniform, Proper Attire, and Grooming',
            'content' => '
II. Policies on Identification Cards, School Uniform, Proper Attire, and Grooming

General Policy Statement
The policies governing Identification Cards, School Uniform, Proper Attire, and Grooming are established to promote a strong sense of discipline, unity, and professionalism within the academic community of i-Link College of Science and Technology, Inc. These guidelines reflect the institution\'s commitment to upholding standards of decorum, respect, and integrity while ensuring the safety and security of its students.

All students are required to wear the Official School ID with the Official Prescribed Lanyard visibly at all times while on campus and during official school activities. This measure ensures proper identification and promotes a sense of belonging and security within the institution. Additionally, students must adhere to the Prescribed School Uniform and Attire guidelines on designated days, reflecting the institution\'s values of professionalism and respect for tradition.

To further enhance the learning environment, students are also expected to maintain Proper Grooming standards that exemplify cleanliness, modesty, and respect for school policies. This includes well-kept hair, polished shoes, and neat presentation, which contribute to the institution’s image of professionalism and discipline.

Compliance with these policies is always expected to maintain the institution\'s positive image and academic integrity. Students are encouraged to take pride in their appearance, recognizing that they represent both the institution and the values it upholds. By adhering to the prescribed dress code, proper grooming, and carrying valid identification, students demonstrate respect for themselves, their peers, and the i-Link CST community.

This collective adherence fosters an environment that supports learning, reinforces a culture of professionalism, and prepares students for their roles as future leaders and professionals in their respective fields. Non-compliance with these policies may result in appropriate disciplinary measures to uphold the integrity and values of the institution.

II.1. The School Identification Card
The School Identification Card (ID) primarily identifies enrolled students at i-Link CST. It is an essential part of campus security and student accountability. All students are required to secure their ID upon enrollment, as it grants access to school facilities and participation in various institutional activities. Students are expected to adhere to the following regulations:

II.1.1. Issuance of the School ID
The request for a School ID is processed upon official enrollment as part of the last enrollment process. All students must have their identification card picture taken by the official personnel in ID Processing. Once processing is complete and all requirements are met, the school ID will be released on a scheduled date to be formally announced by the Office of Student Affairs.

II.1.2. Re-issuance of the School ID
In the event of loss, damage, or defacement of the School Identification Card (ID), the student may request re-issuance through the Office of Student Affairs. The following steps must be observed to facilitate the re-issuance process:
1. Submission of an Affidavit of Loss or Damage Report
a. For lost IDs, the student is required to submit a notarized Affidavit of Loss indicating the circumstances surrounding the loss.
b. For damaged or defaced IDs, a Damage Report signed by the student and verified by the Office of Student Affairs is necessary.
2. Payment of the Re-issuance Fee. A nonrefundable re-issuance fee, the amount of which depends on the approved school fees for the current semester and year, must be settled at the Cashier\'s Office. An official receipt must be presented during the application for re-issuance.
3. Filing of Re-issuance Request Form.
4. Processing and Release.
5. Verification and Confirmation. Upon release, the student must verify the details on the new ID and confirm receipt by signing the ID Re-issuance Logbook at the Office of Student Affairs.
The re-issued School ID is considered an official document of the institution. Any tampering, unauthorized lending, or misuse is subject to disciplinary action as per the Student’s Code of Conduct and Discipline.

II.1.3. No ID, No Entry Policy
The institution strictly enforces a "No ID, No Entry Policy." All students are required to present their valid School Identification Card (ID) upon entering the school premises and during official activities both inside and outside the campus. This measure ensures the security and safety of all academic community members and reinforces the institution\'s commitment to proper identification and accountability.

II.1.4. Mandatory Wearing of School ID
All students are required to wear their School Identification Card at all times while on the school campus. The ID must also be visibly worn when students participate in off-campus activities sanctioned by the school.
Failure to wear the School ID will be recorded and monitored. Any student caught not wearing their School ID three (3) times within a semester, term, or year shall have it recorded as one (1) officially unexcused absence. For every three (3) additional instances of not wearing the School ID after the initial count, one (1) unexcused absence will be recorded.

II.1.5. Non-Transferability and Prohibited Acts
The School Identification Card (ID) is strictly non-transferable. Tampering, defacement, unauthorized use, or lending the ID to others, whether to students or non-students, is considered a violation of the Student Code of Conduct and Discipline and an act of dishonesty. Such actions compromise the security and integrity of the school community and are therefore strictly prohibited.
Additionally, ID Lanyards must strictly adhere to the school\'s issued design and branding. Lanyards from other schools, institutions, or non-official sources are not permitted.
Violations shall be subject to disciplinary action as outlined in the Student Code of Conduct and Discipline.

II.1.6. Confiscation of School ID
The School ID may be confiscated by persons in authority when a student is found violating school rules and regulations in flagrante delicto (caught in the act). This includes, but is not limited to:
1. Not wearing the School ID within the premises during and outside class hours or official activities.
2. Not using the official prescribed School Lanyard.
3. Violation of the No ID, No Entry Policy, unless the student has been officially issued a Temporary Gate Pass or Provisional Access Permit.
4. Unauthorized alterations or misuse of the School ID, such as lending to others, defacing, or tampering.
5. Violations of the School Uniform and Good Grooming Policy.
6. Any other Violations prescribed in this Student Handbook and the Code of Student Conduct and Discipline.

II.2. The Official School Uniform and Good Grooming Policy
General Policy Statement
The Official School Uniform represents the institution\'s values, unity, and commitment to academic excellence. All students must wear the prescribed school uniform at all times while on campus and during official school-sanctioned events. This policy aims to promote discipline, professionalism, and a sense of belonging among students, reflecting the institution\'s standards of decorum and respect.
The proper wearing of the school uniform includes adherence to guidelines on presentation, neatness, and appropriate grooming. Students are expected to maintain their uniforms in good condition, ensuring they are clean, well-kept, and free from unauthorized alterations.

II.2.2.5. Good Grooming Policy
The Good Grooming Policy of i-Link CST is established to uphold the institution\'s standards of professionalism, discipline, and respect. All students are expected to maintain a clean, well-kept, and appropriate appearance at all times as a reflection of the institution’s core values and commitment to excellence.
- Neat Appearance
- Nail Hygiene
- Facial Hair for Males (Clean-shaven)
- Makeup Regulations (Modest, natural-looking)
- Grooming and Styling (Neatly combed, no unnatural colors)
- Body Marks and Tattoos (Not permitted)
- Body Piercings (Prohibited for males)
- Prohibited Accessories (No flashy accessories)
- Body Odor and Hygiene
',
            'attachment' => null,
        ]);

        // 3. Social Media Engagement Policy
        Handbook::create([
            'title' => 'Chapter III: Social Media Engagement Policy',
            'content' => '
III. Social Media Engagement Policy

General Policy Statement
The use of social media and online networking platforms is an integral part of modern communication. At i-Link College of Science and Technology, Inc., responsible and respectful online behavior is expected from all students, faculty, and staff.
Social media interactions should reflect the values of integrity, respect, and professionalism within and beyond the institution\'s virtual spaces. While the institution acknowledges the right to freedom of expression, this freedom carries with it the responsibility to exercise respect, avoid harm, and uphold the values of the academic community.

III.1. Core Principles
The Social Media Policy at i-Link CST is established to promote a safe, respectful, and responsible digital environment. This policy is rooted in the institution\'s values of academic integrity, respect, responsibility, and community harmony.
1. We are i-Link, we are members of a Respectful Community
2. As Filipino Citizens and Global Participants
3. Responsibility and Accountability
4. Freedom of Expression with Responsibility
5. Parents and Guardians as Partners
6. THINK before you click! (True, Helpful, Inspiring, Necessary, Kind)
7. Teachers as Digital Role Models
8. Promoting Safe Spaces for Dialogue

III.2. Scope
This policy covers all social media communications and interactions in public and private forums that involve, represent, or relate to i-Link College of Science and Technology, Inc. and its community members, regardless of the platform used. It applies to all i-Link CST students across all academic levels.

III.3. Prohibited Conduct
The following activities are strictly prohibited on social media platforms and digital communication channels:
III.3.1. Unauthorized Recording and Distribution of Images or Videos (Anti-Photo and Voyeurism Act of 2009)
III.3.2. Cybercrime and Online Misconduct
- Cyberbullying and Online Harassment
- Identity Theft and Hacking
- Spreading Malicious Software or Malware
- Online Libel and Defamation
III.3.3. Privacy Violations and Data Protection
III.3.4. Bullying and Digital Harassment (Anti-Bullying Act of 2013)
III.3.5. Libelous and Defamatory Content Against the Institution or Its Personnel
III.3.6. Hate Speech and Offensive Content
III.3.7. Impersonation or Misrepresentation
III.3.8. Academic Dishonesty (Sharing answers, cheating online)

III.4. Feedback Mechanism
The institution encourages students to engage constructively with one another and with the administration through appropriate and acceptable channels of communication.
Formal Channels: Faculty Members, Program Heads, OSAS, Registrar\'s Office, Guidance Office, Campus Security Office.
Express Channels: Email: feedback@ilinkcst.edu.ph, Facebook Page: i-Link CST Official
',
            'attachment' => null,
        ]);

        // 4. Discipline and Conduct Policy (Partial)
        Handbook::create([
            'title' => 'Chapter IV: Discipline and Conduct Policy',
            'content' => '
IV. Discipline and Conduct Policy

At i-Link College of Science and Technology, every student deserves an environment where they can thrive, learn, and grow into tomorrow\'s leaders. Discipline isn\'t about punishment—it\'s about creating a community where respect, responsibility, and excellence flourish naturally. The Discipline and Conduct Policy upholds the academic community\'s highest standards of conduct, integrity, and respect.

IV.1. Core Principles
1. We are i-Link, we are members of a Respectful Community.
2. Responsibility and Accountability.
3. Integrity in Academic and Personal Conduct.
4. Positive Digital Citizenship.
5. Community Engagement and Harmony.

IV.3. Prohibited Conduct and Sanctions
IV.3.1. Academic Integrity and Misconduct
Academic misconduct is any form of dishonest or unethical behavior that compromises the principles of fairness, trust, and integrity in the learning environment.
IV.3.1.1. Cheating
Cheating is any intentional act of dishonesty aimed at gaining unfair academic advantage. This includes possessing unauthorized materials, altering examination answers, giving or receiving unauthorized assistance, stealing exam materials, allowing another student to replicate answers, Falsification and Fabrication, Ghostwriting, Collusion, and Duplicate Submissions.
IV.3.1.2. Plagiarism
Plagiarism is presenting someone else\'s work, ideas, or expressions as one’s own without proper acknowledgment.
IV.3.1.3. Misuse of Academic Resources
Misuse of academic resources refers to the intentional tampering, destruction, or unauthorized handling of learning materials, equipment, or facilities provided by i-Link CST.

IV.3.2. Non-Academic Misconduct
IV.3.2.1. Minor Offenses
1. Non-compliance with Campus Security and Personnel Instructions.
2. Smoking and Vaping.
3. Improper Wearing of Prescribed Uniforms, Casual Attire, or Course Uniforms.
4. Improper Wearing of School Identification Cards.
5. Failure to Present Identification upon Request.
6. Late ID Validation.
7. Loitering, Unauthorized Presence in Restricted Areas.
8. Reckless Behavior and Endangerment.
9. Disturbing Campus Peace and Order.
10. Use of Offensive Language or Inappropriate Behavior.
11. Littering and Improper Waste Disposal.
12. Use of Mobile Devices During Class Sessions.
13. Unauthorized Use of Institutional Facilities.
14. Disregard for Campus Parking Regulations.
15. Disrespecting Campus Property.
16. Improper Conduct During Assemblies and Events.
17. Misrepresentation and Providing False Information.
18. Inappropriate Public Display of Affection.

IV.3.2.2. Grave Offenses
1. Disrespect or Insubordination (Grave threats, direct assault, etc.)
2. Voyeurism (Secretly observing or recording individuals in private settings).
3. Possession and Dissemination of Obscene or Inappropriate Materials.
4. Malversation or Misuse of Institutional Resources.
5. Involvement in Physical Altercations (On-Campus or Off-Campus).
...(Text truncated at this point)...
',
            'attachment' => null,
        ]);
    }
}
