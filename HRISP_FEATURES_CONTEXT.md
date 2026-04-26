# HRISP Java Spring Boot — Complete Feature & Codebase Context
> Use this file to recall the full feature set of the HRIS Java project when planning or building the PHP/MySQL replication.
> Scanned from: `C:\Users\Habib\IdeaProjects\hrisp-web\hrisp-web`

---

## Project Overview

- **Framework:** Spring Boot 2.2.4 (Java 17)
- **Packaging:** WAR (deployable to Tomcat or run embedded)
- **Database:** MySQL — database name `hris_java`
- **Dev Port:** 8084 — context path `/hrisp`
- **Prod Port:** 8083 — context path `/hrisp`
- **Default URL (dev):** `http://localhost:8084/hrisp`
- **Active Profile by default:** `dev`
- **DB credentials (dev):** username=`root`, password=`muning0328`
- **Timezone:** Asia/Manila
- **Template engine:** Thymeleaf
- **Security:** Spring Security (NoOpPasswordEncoder — plain text passwords currently)
- **Reports:** JasperReports (.jasper compiled templates)
- **File uploads:** stored on disk at `/hrisp/uploads`
- **Email:** Spring Boot Mail (configured but not fully wired in all flows)

---

## Authentication & Security

### Login
- URL: `GET /login`, `POST /login`
- Redirects to `/dashboard` on success
- Redirects to `/login?error` on failure
- Public routes: `/login`, `/logout`, `/error`, `/reports/**`, `/api/**`
- Static assets ignored by security: `/assets/**`, `/global_assets/**`, `/js/**`, `/css/**`, `/images/**`

### Roles (userType field on Employee)
- `HRADMIN` — HR Administrator, full access
- `EMPLOYEE` — Self-service view only (profile mode)
- The `Employee` entity itself implements `UserDetails` — the employee record IS the user account
- `SysUser` is a separate user table (light usage)

### Session
- On `/dashboard`, the authenticated `Employee` object is stored in session as `actorObj`
- Employee URLs are protected by a hash: `/employee/{id}/{showMode}/{empHashCode}`
- `empHashCode` is a 20-character alphanumeric UUID-derived hash generated on employee creation

### Change Password
- Route: managed by `ChangePasswordController`
- Template: `changepassword/change-password.html`
- Model: `ChangePassword.java` (firstName, lastName, currentPassword, newPassword, confirmPassword)

---

## Entity Hierarchy

### Person (MappedSuperclass — base for Employee and FamilyBg)
Fields inherited by all person entities:
- `firstName` (required)
- `lastName` (required)
- `middleName`
- `prefix` (e.g. Dr., Engr.)
- `suffix` (e.g. Jr., III)
- `birthPlace`
- `gender` (required)
- `birthdate` (LocalDate, format yyyy-MM-dd) — has `+1 day` correction applied on read
- Computed: `getFullName()` → "FirstName M. LastName Suffix"
- Computed: `getDisplayName()` → "Prefix FirstName M. LastName Suffix"
- Computed: `getAge()` → integer years from birthdate to today
- Extends `Auditable` (createdDate, lastModifiedDate — JPA auditing)

---

## Module 1: Employee (Personal Info / PDS Page 1)

**Entity:** `Employee extends Person implements UserDetails`
**Table:** `employee`
**Controller:** `EmployeeController`
**Templates:** `employee/employee-list.html`, `employee/pds/personnal-info.html`

### All Fields
| Field | Type | Notes |
|---|---|---|
| id | long (PK) | auto-generated |
| empHashCode | String | 20-char UUID hash, URL security |
| empNo | String | Employee number |
| username | String | unique constraint |
| password | String | plain text currently |
| assumptiondate | LocalDate | date assumed position |
| plantillaNo | String | plantilla item number |
| titleSuffix | String | |
| civilStatus | String | Single/Married/Widowed/etc. |
| height | String | |
| weight | String | |
| religion | String | |
| bloodType | String | |
| gsisBpNo | String | GSIS BP number |
| gsisPolicyNo | String | |
| gsisIdNo | String | |
| pagibigNo | String | Pag-IBIG number |
| philhealthNo | String | PhilHealth number |
| sssNo | String | SSS number |
| tin | String | Tax Identification Number |
| citizenship | String | |
| countryOfOrigin | String | |
| birthPlace | String | from Person base |
| telNo | String | telephone number |
| email1 | String | validated @Email |
| email2 | String | validated @Email |
| mobileNo1 | String | |
| mobileNo2 | String | |
| status | String | default = "ACTIVE" |
| district | District (FK) | |
| division | Division (FK) | |
| employeeStatus | EmployeeStatus (FK) | |
| positionTitle | PositionTitle (FK) | |
| userType | String | required — "HRADMIN" or "EMPLOYEE" |
| profilePhoto | String | URL path to uploaded photo |
| houseno1 | String | residential address |
| street1 | String | residential |
| subdivision1 | String | residential |
| brgy1 | String | residential |
| city1 | String | residential |
| province1 | String | residential |
| zipcode1 | String | residential |
| houseno2 | String | permanent address |
| street2 | String | permanent |
| subdivision2 | String | permanent |
| brgy2 | String | permanent |
| city2 | String | permanent |
| province2 | String | permanent |
| zipcode2 | String | permanent |

### Transient (not persisted)
- `showMode` — "HRADMIN" or "PROFILE" (controls UI view)
- `confirmPassword`
- `photoFile` (MultipartFile)
- `familyBgList`
- `pdsCountDto` — counts of all PDS sub-sections

### PdsCountDto (transient, counts per employee)
- familyBgCount, educationalBgCount, eligibilityCount, workExperienceCount
- voluntaryWorkCount, learningDevCount, otherInfoCount, otherInfoQuestionsCount
- referencesCount, govIdCount

### Key Routes
| Method | URL | Action |
|---|---|---|
| GET | /employee-list | List all employees (excludes ID=1, admin) |
| GET | /employee/{id}/{showMode}/{empHashCode} | View/edit PDS personal info |
| POST | /addEmployee | Create new employee |
| POST | /editEmployee | Edit employee |
| POST | /saveProfile | Save from employee self-profile |
| GET | /employee/datalist | REST — returns all employees as JSON for datalist |

### Business Logic
- On **add**: username auto-generated as `firstName[0] + lastName`, password defaults to `"123456"`
- On **edit**: password and username preserved from old record; empHashCode preserved
- Birthdate gets `+1 day` correction on save (timezone fix)
- Duplicate check: by `firstName + lastName + birthdate`
- `empHashCode` generated once on creation using UUID → 20-char alphanumeric

---

## Module 2: Family Background (PDS Section)

**Entity:** `FamilyBg extends Person`
**Table:** `family_bg`
**Controller:** `FamilyBgController`
**Template:** `employee/pds/family-background.html`

### Fields (beyond Person base)
- `relationship` — Spouse / Father / Mother / Child / etc.
- `isDeceased` (boolean)
- `tin`
- `occupation`
- `employer`
- `telNo`
- `businessAdd`
- `employmentStatus`
- `mobileNo`
- `employee` (FK → Employee)
- `showMode` (transient)

---

## Module 3: Educational Background (PDS Section)

**Entity:** `EducationalBackground`
**Table:** `educational_background`
**Controller:** `EducationalBackgroundController`
**Template:** `employee/pds/educational-background.html`

### Fields
- `degreeLevel` (FK → DegreeLevel) — Elementary/Secondary/Vocational/College/Graduate
- `school` (FK → School)
- `degreeCourse` (FK → DegreeCourses)
- `scholarship` (FK → Scholarship)
- `academicHonors` (FK → AcademicHonors)
- `startDate` (LocalDate)
- `endDate` (LocalDate)
- `upToPresent` (boolean)
- `unitsEarned` (String)
- `yearGraduated` (int)
- `remarks`
- `attachmentUrl` — path to uploaded diploma/TOR file
- `attachedFile` (transient MultipartFile)
- `employee` (FK)
- Computed: `getDateToString()` → "MONTH YEAR - MONTH YEAR"

---

## Module 4: Civil Service Eligibility (PDS Section)

**Entity:** `CivilServiceEligibility`
**Table:** `civil_service_eligibility`
**Controller:** `CivilServiceEligibilityController`
**Template:** `employee/pds/eligibility.html`

### Fields
- `eligibility` (String — free text or selected)
- `otherEligibility` (String)
- `rating` (String — exam rating/score)
- `examYear` (int)
- `examMonth` (String)
- `examDay` (int)
- `placeOfExam` (String)
- `licenseNo` (String)
- `licenseValidityDate` (LocalDate)
- `licenseReleaseDate` (LocalDate)
- `attachmentUrl` — path to uploaded eligibility certificate
- `attachedFile` (transient MultipartFile)
- `employee` (FK)
- Computed: `getExamDate()` → "Month Year"

---

## Module 5: Work Experience (PDS Section)

**Entity:** `WorkExperience`
**Table:** `work_experience`
**Controller:** `WorkExperienceController`
**Template:** `employee/pds/work-experience.html`

### Fields
- `dateFrom` (LocalDate)
- `dateTo` (LocalDate)
- `upToPresent` (boolean)
- `positionTitle` (String, required — @NotBlank)
- `department` (String)
- `officeName` (String)
- `immediateSupervisor` (String)
- `jobDescription` (String)
- `salary` (BigDecimal)
- `salaryGrade` (int)
- `stepNo` (int)
- `appointmentStatus` (String — Permanent/Casual/Contractual/etc.)
- `govtOffice` (String — yes/no)
- `remarks` (String)
- `employee` (FK)
- Computed: `getInclusiveDates()` → "dateFrom - dateTo"
- Computed: `getFormattedSalary()` → "#,###,###.00"

---

## Module 6: Voluntary Work (PDS Section)

**Entity:** `VoluntaryWork`
**Table:** `voluntary_work`
**Controller:** `VoluntaryWorkController`
**Template:** `employee/pds/voluntary-work.html`

### Fields
- `orgName` (String, required — @NotBlank)
- `dateFrom` (LocalDate)
- `dateTo` (LocalDate)
- `upToPresent` (boolean)
- `noHours` (int — number of hours)
- `natureOfWork` (String)
- `address` (String)
- `employee` (FK)
- Computed: `getInclusiveDates()` → "MMMM yyyy - MMMM yyyy"

---

## Module 7: Learning & Development (PDS Section)

**Entity:** `LearningAndDevelopment`
**Table:** `learning_development`
**Controller:** `LearningAndDevelopmentController`
**Template:** `employee/pds/learning-development.html`

### Fields
- `titleOfSeminar` (String — title of training/seminar)
- `trainingCourseDesc` (String — description)
- `dateFrom` (LocalDate)
- `dateTo` (LocalDate)
- `upToPresent` (boolean)
- `noHours` (Integer)
- `learningType` (String — e.g. Managerial/Technical/Foundational)
- `providers` (String — sponsoring agency/org)
- `employee` (FK)
- Computed: `getInclusiveDates()` → "dateFrom - dateTo"

---

## Module 8: Other Info (PDS Section)

**Entity:** `OtherInfo`
**Table:** `other_info`
**Controller:** `OtherInfoController`
**Template:** `employee/pds/other-info.html`

### Fields
- `specialSkill` (String — special skills/hobbies)
- `nonAcademic` (String — non-academic distinctions/recognition)
- `membershipInAssociation` (String — org memberships)
- `employee` (FK)

---

## Module 9: Other Info Questions (PDS Section — CS Form 212 Page 4)

**Entity:** `OtherInfoQuestion`
**Table:** `other_info_question`
**Controller:** `OtherInfoQuestionController`
**Template:** `employee/pds/other-info-question.html`

### Fields (all String — Yes/No answers + details)
These correspond to the security/background questions on CS Form 212:
- `questionOneThird` + `questionOneThirdIfYes` — 3rd degree relation in gov't service
- `questionOneFourth` + `questionOneFourthIfYes` — 4th degree relation in gov't service
- `questionTwoA` + `questionTwoAIfYes` — guilty of admin offense
- `questionTwoB` + `questionTwoBIfYes` + `questionTwoBMonth/Day/Year/StatusCase` — criminal charge
- `questionThree` + `questionThreeIfYes` — convicted by court
- `questionFour` + `questionFourIfYes` — separated from service
- `questionFive` + `questionFiveIfYes` — candidate in election
- `questionSix` + `questionSixIfYes` — resigned from gov't to avoid admin case
- `questionSevenA` + `questionSevenAIfYes` — immigrant/permanent resident abroad
- `questionEight` + `questionEightType/Id/ValidityDate/Attachment` — gov't issued ID (Item 40B)
- `questionNine` + `questionNineIfYes` — (Item 40C)
- `questionTen` + `questionTenIfYes` — (Item 40A)
- `employee` (FK)

---

## Module 10: References (PDS Section)

**Entity:** `EmpReferences`
**Table:** `emp_references`
**Controller:** `EmpReferencesController`
**Template:** `employee/pds/references.html`

### Fields
- `referenceName` (String)
- `positionTitle` (String)
- `companyAddress` (String)
- `companyContactNo` (String)
- `employee` (FK)

---

## Module 11: Government Issued ID (PDS Section)

**Entity:** `GovermentIssuedId`
**Table:** `government_issued_id`
**Controller:** `GovermentIssuedIdController`
**Template:** `employee/pds/gov-issued-id.html`

### Fields
- `govermentIssuedName` (String — name of the ID, e.g. Passport, Driver's License)
- `idNo` (String)
- `issuanceDate` (LocalDate)
- `placeOfIssuance` (String)
- `employee` (FK)

---

## Module 12: Service Record

**Entity:** `ServiceRecord`
**Table:** `service_record`
**Controller:** `ServiceRecordController`
**Templates:** `employee/service-record/employee-service-record.html`, `employee/service-record/my-service-record.html`

### Fields
- `dateFrom` (LocalDate)
- `dateTo` (LocalDate)
- `isPresent` (String — toggle if currently in position)
- `designation` (String)
- `employeeStatus` (FK → EmployeeStatus)
- `positionTitle` (FK → PositionTitle)
- `levelOfPosition` (String)
- `employee` (FK → Employee)
- `salary` (Double)
- `station` (String)
- `branch` (String)
- `lvAbs` (String — leave of absence)
- `separationCause` (String — mode of separation)
- `separationDate` (LocalDate)
- `plantillaNo` (String)
- `pageNo` (Integer)
- `employmentStatusNotes` (String)
- `positionTitleNotes` (String)
- `vice` (String — replacing whom)
- `statusOfSepeparation` (String)
- `statusOfAppointment` (String — nature of appointment / mode of accession)
- `salaryGrade` (Integer)
- `stepInc` (Integer)
- `signingDate` (LocalDate)
- `entranceDate` (LocalDate)
- `eligibility` (String)
- `officeAssignment` (String)
- `remarks` (String)
- `district` (String)
- `experience` (String)
- `training` (String)
- `publicationDateFrom` (LocalDate) — for RAI report
- `publicationDateTo` (LocalDate) — for RAI report
- `modeOfPublication` (String)
- `validateInv` (String)
- `dateOfActionCscAction` (LocalDate)
- `dateOfReleaseCscAction` (LocalDate)
- `agencyReceivingOfficer` (String)
- `showMode` (transient)

### Routes
| Method | URL | Action |
|---|---|---|
| GET | /employee-service-record/{employeeId}/{empHashCode} | HR Admin view |
| GET | /my-service-record/{employeeId}/{empHashCode} | Employee self-view |
| POST | /addServiceRecord | HR Admin saves |
| POST | /addMyServiceRecord | Employee self saves |

### Notes
- All date fields get `+1 day` correction on list load (timezone fix)
- Sorted by `dateFrom DESC`
- `ServiceRecordReportRequest` entity stores report generation requests

---

## Module 13: Clearance

**Entity:** `Clearance`
**Table:** `clearance`
**Controller:** `ClearanceController`
**Templates:** `employee/clearance/my-clearance.html`, `employee/clearance/clearance-list.html`

### Fields
- `addressTo` (String — who the clearance is addressed to)
- `purpose` (String — purpose of clearance, e.g. "Resignation", "Retirement")
- `otherPurpose` (String — if purpose is "Other")
- `status` (String — "SUBMITTED" on create, then admin can update)
- `approvedBy` (String — name of approver)
- `transDate` (LocalDate — auto-set to today on new submission)
- `effectiveDate` (LocalDate)
- `employee` (FK → Employee)

### Routes
| Method | URL | Action |
|---|---|---|
| GET | /clearance-list | HR Admin clearance list |
| GET | /myclearance/{employeeId}/{empHashCode} | Employee view own clearances |
| POST | /addClearance | HR Admin saves |
| POST | /addMyClearance | Employee submits clearance request |

---

## Module 14: Clearance Approvers Settings

**Entity:** `ClearanceApprovers`
**Table:** `clearance_approvers`
**Controller:** `ApproversController`
**Template:** `employee/clearance/clearance-approvers.html`

### Fields (all String — names and positions of signatories per department)
- `immediateSupervisor`, `headOfOffice`
- Admin: `adminPersonA/B/C`, `adminPositionA/B/C`
- Library: `libraryPersonA/B`, `libraryPositionA/B`
- Finance: `financePersonA/B/C`, `financePositionA/B/C`
- Professional: `professionalPersonA`, `professionalPositionA`
- Section 4: `section4Person`, `section4Position`
- Footer: `footerPerson1/2`, `footerPosition1/2`

### Notes
- Single record (findFirst) — only one config at a time
- Used to populate the Clearance Form PDF

---

## Module 15: Service Record Signatory Settings

**Entity:** `ServiceRecordSignatory`
**Table:** `service_record_signatory`
**Controller:** `ApproversController`
**Template:** `employee/clearance/service-record-signatory.html`

### Fields
- `signatory` (String — name of signatory)
- `position` (String — position/title of signatory)

### Notes
- Single record (findFirst)
- Printed at the bottom of Service Record PDF

---

## Module 16: Appointments

**Entity:** `Appointment`
**Table:** `appointment`
**Controller:** `AppointmentController`
**Templates:** `employee/appointments/employee-appointment-record.html`, `employee/appointments/employee-list-appointments.html`

### Fields
- `plantillaNo` (String)
- `signingDate` (LocalDate)
- `pageNo` (int)
- `employee` (FK → Employee)
- `positionTitle` (FK → PositionTitle)
- `status` (String)
- `salary` (Double)
- `vice` (String — replacing whom)
- `statusOfSepeparation` (String)
- `statusOfAppointment` (String — Permanent/Casual/Contractual/etc.)
- `salaryGrade` (int)
- `stepInc` (int)
- `entranceDate` (LocalDate)
- `eligibility` (String)
- `highestEducAttainment` (String)
- `officeAssignment` (String)
- `remarks` (String)
- `district` (String)
- `experience` (String)
- `training` (String)

### Routes
| Method | URL | Action |
|---|---|---|
| GET | /appointments/{employeeId}/{empHashCode} | View appointments |

---

## Module 17: 201 Documents (File Management)

**Entity:** `Docs201`
**Table:** `docs_201`
**Controller:** `Docs201Controller`
**Template:** `employee/docs201/docs201.html`

### Fields
- `transDate` (LocalDate)
- `remarks` (String)
- `employee` (FK → Employee)
- `documentType` (FK → DocumentType)
- `docFileUrls` (List<String> — @ElementCollection, stores multiple file paths)
- `docFiles` (transient — MultipartFile[] for upload)

### File Upload Service
- `StorageService` / `StorageController` handle uploads
- Stored at `/hrisp/uploads/` on server
- `FileDTO` holds `fileName`, `downloadUri`, `fileType`, `size`
- Max file size: 5MB per file, 5MB per request

---

## Module 18: Reports (JasperReports)

**Controller:** `ReportsController`
**Jasper files:** `src/main/resources/jasper/reports/`
**Templates:** `employee/reports/`

### Available Reports
| Report | Jasper File | Description |
|---|---|---|
| PDS Page 1 | `PDS1.jasper` | CS Form 212 — Personal Info |
| PDS Page 2 | `PDS2.jasper` | CS Form 212 — Family/Education/Eligibility |
| PDS Page 3 | `PDS3.jasper` | CS Form 212 — Work Exp/Voluntary/L&D |
| PDS Page 4 | `PDS4.jasper` | CS Form 212 — Other Info/Questions/References |
| PDS Full | `PDSFULL.jasper` or merged via `JasperReportsMerger` | All 4 pages merged into one PDF |
| Service Record | `NewServiceRecord.jrxml` (compiled) | Full employment history |
| Certification | `CERTIFICATION.jasper` | Employee certificate of employment |
| Clearance Form | `Clearance-Form-CSC-Form.jasper` | CSC clearance form |
| CS Form No. 4 | `CSFORMNo4.jasper` | Appointment form |
| CS Form No. 33-B | `CSFORMNo33-B.jasper` | |
| RAI Report (p1) | `RAI-REPORT.jasper` | Report on Appointments Issued |
| RAI Report (p2) | `RAI-REPORT-PAGE2.jasper` | RAI second page |
| Accession Report | (via ReportsController) | Report on Accession |
| Separation Report | `REPORT-ON-SEPARATION.jasper` | Report on Separation |
| Position Desc Form | `PositionDescFormPage1.jasper` + `Page2.jasper` | Position Description Form |

### Report DTOs
**EmpCertificateDto:** empId, empName, empStatus, position, rate, signatory, signatoryPosition

**RaiReportDto:** agency, resolutionNo, cscOfficer, receivedDate, month, year,
signatory1-5, position1-5, hrmoComment1-7, cscfoComment1-7

**ReportOnAccessionSeparationDto:** empName, positionTitle, effectivityDate,
modeOfAccession, salaryGrade, statusOfAppointment, levelOfPosition

### Report Templates (HTML pages for input)
- `employee/reports/employee-list-reports.html` — select employee + report type
- `employee/reports/rai-report.html` — input form for RAI report parameters
- `employee/reports/accession-report.html`
- `employee/reports/separation-report.html`

---

## Module 19: REST API Endpoints

**Controller:** `RestApiController` (`@RestController`)
**Base path:** accessible at `/api/**` (public, no auth) via Spring Data REST
**Custom REST routes:**

| Method | URL | Returns |
|---|---|---|
| GET | /employee/datalist | All employees as JSON (FlexDatalistResult) |
| GET | various `/api/**` | Spring Data REST auto-endpoints for all @RepositoryRestResource repos |

---

## System Settings Modules (Lookup/Reference Data)

All follow the same pattern: CRUD list page, add modal, edit modal.

### 1. Division
**Table:** `division` | **Controller:** `DivisionController`
- `divisionName` (required)
- `orderNo` (int — display order)
- `approver1` (FK → Employee)
- `approver2` (FK → Employee)

### 2. District
**Table:** `district` | **Controller:** `DistrictController`
- `districtName` (required)

### 3. Position Title
**Table:** `position_titles` | **Controller:** `PositionTitleController`
- `positionTitleName` (required)
- `departmentCode` (String)
- `isActive` (boolean, default true)

### 4. Position Description Form
**Table:** `position_description_form` | **Controller:** `PositionDescriptionFormController`
**Template:** `system-settings/position-title/position-description.html`
- Linked to `PositionTitle` (FK)
- `itemNumber`, `salaryGrade`
- LGU classification: `lguProvince`, `lguCity`, `lguMunicipality`, `lguClass1-6`, `lguClassSpecial` (all boolean)
- Agency: `departmentAgency`, `bureauOffice`, `branchDivision`, `workstation`
- Appropriation: `presentAppropriationAct`, `previousAppropriationAct`, `salaryAuthorized`, `otherCompensation`
- Supervisor info: `immediateSupervisorTitle`, `nextHigherSupervisorTitle`
- Supervised staff: `supervisedStaffPositionTitle`, `supervisedStaffItemNumber`
- `equipmentUsed` (@Lob)
- Contacts (internal exec/supervisor/non-supervisor/staff, external public/agencies/others)
- Work conditions (office/field/others)
- `functionOfUnit`, `jobSummary` (@Lob)
- Qualification Standards: `qsEducation`, `qsExperience`, `qsTraining`, `qsEligibility` (all @Lob)
- Competencies: `coreCompetencies`, `coreCompetencyLevel`, `leadershipCompetencies`, `leadershipCompetencyLevel` (all @Lob)
- Duties: `dutyDescription`, `dutyPercentage`, `dutyCompetencyLevel` (all @Lob)
- Signatories: `employeeName`, `supervisorName`

### 5. Salary Grade
**Table:** `salary_grade` | **Controller:** `SalaryGradeController`
- `salaryGradeGroup` (String)
- `salaryGradeNumber` (Integer)
- `isActive` (boolean, default true)

### 6. Employee Status
**Table:** `employee_status` | **Controller:** `EmployeeStatusController`
- `employeeStatusName` (required)
- `payrollBehavior` (String — enum-like: from `PayrollBehavior.java`)
- `employmentType` (String — from `EmploymentType.java`)
- `isActive` (boolean, default true)

### 7. Eligibility (CSC types)
**Table:** `eligibility` | **Controller:** `EligibilityController`
- `eligibilityName` (String)

### 8. Schools
**Table:** `schools` | **Controller:** `SchoolController`
- `schoolName`

### 9. Degree Courses
**Table:** `degree_courses` | **Controller:** `DegreeCoursesController`
- `degreeCourse`

### 10. Degree Levels
**Table:** `degree_levels` | **Controller:** `DegreeLevelController`
- `degreeLevelName`

### 11. Academic Honors
**Table:** `academic_honors` | **Controller:** `AcademicHonorsController`
- `honorName`

### 12. Scholarships
**Table:** `scholarship` | **Controller:** `ScholarshipController`
- `scholarshipName`

### 13. Professions
**Table:** `profession` | **Controller:** `ProfessionController`
- `professionName`

### 14. Learning Types
**Table:** `learning_type` | **Controller:** `LearningTypeController`
- `learningTypeName`

### 15. Levels
**Table:** `levels` | **Controller:** `LevelController`
- `levelName`

### 16. Document Types
**Table:** `document_type` | **Controller:** `DocumentTypeController`
- `documentTypeName`

---

## Navigation & Dashboard

- `NavController` handles `GET /dashboard`
- On dashboard load: stores authenticated `Employee` object in session as `actorObj`
- Template: `dashboard.html`
- Fragments: `fragments/common.html` — shared layout (navbar, sidebar)

---

## Data Migration

**Service:** `DataMigrationService`
**DTO:** `EmployeeInfoDTO` — used to map CSV/legacy data to Employee entities
- Contains employee info fields for batch import
- `opencsv` library used for CSV parsing

---

## Constants / Enums

- **EmploymentType.java** — employment type values for EmployeeStatus
- **PayrollBehavior.java** — payroll behavior values for EmployeeStatus

---

## URL Patterns Summary

| Section | URL Pattern |
|---|---|
| Dashboard | /dashboard |
| Employee List | /employee-list |
| Employee PDS | /employee/{id}/{showMode}/{empHashCode} |
| Service Record (HR) | /employee-service-record/{id}/{hash} |
| Service Record (self) | /my-service-record/{id}/{hash} |
| Clearance (self) | /myclearance/{id}/{hash} |
| Clearance List | /clearance-list |
| Clearance Approvers | /clearance-approver-settings |
| SR Signatory | /service-record-signatory |
| Appointments | /appointments/{id}/{hash} |
| System Settings | /division-list, /district-list, /position-title-list, etc. |
| Reports | /reports/** |
| Login | /login |
| Change Password | /change-password |

---

## Known Issues / Notes

1. **Lombok build error** — `fork=true` + `--add-opens` in `compilerArgs` was the bug. Fixed by removing those from `pom.xml`.
2. **Passwords are plain text** — `NoOpPasswordEncoder` in use. BCrypt encoder code is commented out.
3. **Date +1 day correction** — Multiple entities have a `+1 day` correction on date reads/writes due to MySQL timezone offset. Applied in controllers via manual `plusDays(1)` calls.
4. **Single tenant** — The system is single-organization. No `organization_id` exists anywhere.
5. **Security questions (Item 40)** in `OtherInfoQuestion` use fields named `questionEight` (40B), `questionNine` (40C), `questionTen` (40A) — the naming is non-intuitive.
6. **Clearance approvers** — Only one set of approvers stored globally (not per-employee or per-department).
7. **Service Record Report Request** — `ServiceRecordReportRequest` entity + repository exists for tracking PDF generation requests but is lightly used.
8. **Backup files** — `.bak` versions of some templates exist (eligibility.html.bak, family-background_backup.html).
9. **Error page** — `error.html` template exists.
10. **WAR packaging** — Tomcat is `provided` scope; can still run via `mvnw spring-boot:run` in dev.

---

## PHP Replication Notes (for HRISP v2)

### Direct Translations
- `@Controller` + `@GetMapping` → PHP function receiving `$_GET` / `$_POST`
- Thymeleaf `th:text`, `th:each`, `th:if` → `<?php echo ?>`, `foreach`, `if`
- Spring Data JPA `findByEmployeeId()` → `SELECT * FROM table WHERE employee_id = ?`
- `@Valid` + `Errors` → PHP `empty()` / `filter_var()` validation
- `MultipartFile` → `$_FILES` + `move_uploaded_file()`
- `RedirectAttributes.addFlashAttribute("msg")` → `$_SESSION['msg']` flash
- Spring Security session → PHP `$_SESSION['user']` + `password_verify()`

### What to Add for v2 (Subscription Model)
New tables needed:
- `organizations` (id, name, slug, email, created_at)
- `subscriptions` (id, organization_id, plan_type, billing_cycle, start_date, end_date, status, amount_paid)
- `subscription_plans` (id, plan_name, max_employees, quarterly_price, yearly_price, features JSON)
- `payments` (id, subscription_id, reference_no, amount, payment_method, paid_at, confirmed_by)
- Add `organization_id` FK to: employee, division, district, position_titles, all system settings tables

### Plan Tiers (Suggested)
| Plan | Max Employees | Quarterly | Yearly | Features |
|---|---|---|---|---|
| Basic | 50 | ₱999 | ₱2,999 | PDS only + PDS print |
| Professional | 200 | ₱2,499 | ₱7,499 | + Service Record, Clearance, 201 Docs, Appointments, All Reports |
| Enterprise | Unlimited | ₱4,999 | ₱14,999 | + Position Desc Form, Data Migration, Multi-admin, API, White-label |

### PDF Reports in PHP
Use **TCPDF** or **mPDF** library to rebuild all 10 Jasper report layouts:
- PDS 4-page merged, Service Record, Certification, Clearance Form,
  CS Form 4, CS Form 33-B, RAI (2 pages), Accession Report, Separation Report, Position Desc Form (2 pages)
