<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCourses extends Command
{
    protected $signature = 'courses:sync';

    protected $description = 'Syncs the master JSON course list into the database';

    public function handle()
    {
        $this->info('Starting Course Sync...');

        // Your Master JSON Data
        $courses = [
            [
                'course_code' => 'DEK3023',
                'course_name' => 'Probability and Statistical Data Analysis',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Statistical Data Analysis', 'Probability Modeling', 'Hypothesis Testing (Z-tests & T-tests)', 'Linear Regression & Correlation Analysis', 'Data Visualization', 'Python (Pandas, SciPy)', 'Microsoft Excel', 'SPSS', 'Analytical Reasoning'],
                'course_content_outline' => ['Week 1: Statistics Types', 'Week 2: Graphing Data', 'Week 3: Central Tendency', 'Week 4: Probability', 'Week 5: Discrete Variables', 'Week 6: Continuous Variables', 'Week 7: Sampling', 'Week 8-9: Estimation', 'Week 10-11: Hypothesis Testing', 'Week 12-13: Inference', 'Week 14: Regression'],
            ],
            [
                'course_code' => 'DEK3033',
                'course_name' => 'Numerical Methods For Computing',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Numerical Analysis', 'MATLAB', 'Root-Finding Algorithms', 'Linear Algebra Solvers', 'Numerical Integration', 'Curve Fitting', 'Error Propagation', 'C++ / Python', 'Algorithm Optimization'],
                'course_content_outline' => ['Week 1: Algorithms', 'Week 2-3: Nonlinear Equations', 'Week 4: Gauss Elimination', 'Week 5: LU Factorization', 'Week 6: Iterative Techniques', 'Week 7-8: Differentiation & Integration', 'Week 9-10: Regression', 'Week 11-12: Interpolation', 'Week 13-14: Projects'],
            ],
            [
                'course_code' => 'DEP3013',
                'course_name' => 'Instructional Technology and Design In Courseware Development',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Instructional Design (ADDIE)', 'Learning Theories', 'Multimedia Learning', 'UI Design', 'Courseware Development', 'Heuristic Evaluation', 'Software Engineering', 'Figma', 'Unity'],
                'course_content_outline' => ['Week 1: Intro', 'Week 2: Learning Theories', 'Week 3-4: Design Models', 'Week 5-6: Multimedia Theory', 'Week 7: UI Design', 'Week 8: Motivation', 'Week 9-11: Methodology', 'Week 12: Evaluation', 'Week 13-14: Assessment'],
            ],
            [
                'course_code' => 'DEP3023',
                'course_name' => 'Models of Instruction',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Instructional Strategy', 'Curriculum Development', 'Learning Styles', 'Blended Learning', 'Assessment Design', 'Instructional Alignment', 'Cooperative Learning', 'LMS Navigation', 'Educational Delivery'],
                'course_content_outline' => ['Week 1: Concepts', 'Week 2-4: Planning I', 'Week 5-7: Planning II', 'Week 8-9: Basic Models', 'Week 10-11: Advanced Models', 'Week 12-13: Best Practices', 'Week 14: Future Models'],
            ],
            [
                'course_code' => 'DEP3063',
                'course_name' => 'Courseware Engineering',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Agile (Scrum)', 'SDLC', 'QA Testing', 'Multimedia Tools', 'IP Rights', 'Software Evaluation', 'Technical Writing', 'Android Dev', 'UI/UX'],
                'course_content_outline' => ['Week 1: Intro', 'Week 2: Methodologies', 'Week 3-7: Development', 'Week 8: IPR', 'Week 9-10: Evaluation', 'Week 11: Packaging', 'Week 12-13: Showcase', 'Week 14: Reporting'],
            ],
            [
                'course_code' => 'DEQ3063',
                'course_name' => 'Software Project Management',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Agile PM', 'SDP Preparation', 'Scheduling (Gantt)', 'Cost Estimation', 'Risk Management', 'Resource Allocation', 'Jira/Trello', 'Feasibility Analysis', 'Project Charters'],
                'course_content_outline' => ['Week 1: Intro', 'Week 2: Concepts', 'Week 3: Initiation I', 'Week 4: Initiation II', 'Week 5-6: Planning', 'Week 7-9: Estimation', 'Week 10-11: Scheduling', 'Week 12: Allocation', 'Week 13: Risk', 'Week 14: SDP'],
            ],
            [
                'course_code' => 'DEQ3093',
                'course_name' => 'Software Configuration Management',
                'next_course_code' => null,
                'category' => 'FOCUS',
                'credit' => 3,
                'associated_skills' => ['Git/SVN', 'Baselining', 'Change Control', 'Auditing', 'Branching/Merging', 'CI/CD', 'Status Accounting', 'Release Mgmt', 'Ethics'],
                'course_content_outline' => ['Week 1: Intro', 'Week 2: SCM History', 'Week 3: Concepts', 'Week 4: Planning', 'Week 5: PM Environment', 'Week 6: Process Mgmt', 'Week 7: Identification', 'Week 8: Change Control', 'Week 9: Status Accounting', 'Week 10: Auditing', 'Week 11: Case Study', 'Week 12: Tools', 'Week 13: Project', 'Week 14: Reflection'],
            ],
            [
                'course_code' => 'DES3043',
                'course_name' => 'Software Design',
                'next_course_code' => 'DEQ3093',
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Software Architecture', 'Mobile App Design', 'RESTful API', 'UI/UX Design', 'OOP Design', 'DB Schema', 'SDD Authoring', 'Figma', 'StarUML'],
                'course_content_outline' => ['Week 1-2: Process & Quality', 'Week 3: Mobile Apps', 'Week 4: Challenges', 'Week 5-6: Web & API', 'Week 7: Patterns', 'Week 8: Architecture 1', 'Week 9: Architecture 2', 'Week 10: Detailed 1', 'Week 11: Detailed 2', 'Week 12: Detailed 3', 'Week 13: Docs 1', 'Week 14: Docs 2'],
            ],
            [
                'course_code' => 'DES3023',
                'course_name' => 'Software Requirements and Specifications',
                'next_course_code' => 'DES3043',
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Requirements Elicitation', 'UML Modeling', 'SRS Documentation', 'Validation', 'Negotiation', 'Change Mgmt', 'Jira/Confluence', 'Enterprise Architect', 'Technical Writing'],
                'course_content_outline' => ['Week 1: Intro', 'Week 2: Types', 'Week 3-4: Elicitation 1', 'Week 5-6: Elicitation 2-3', 'Week 7-8: Analysis', 'Week 9: Modeling', 'Week 10: Documentation', 'Week 11: Validation', 'Week 12: Negotiation', 'Week 13: Mgmt', 'Week 14: Presentation'],
            ],
            [
                'course_code' => 'DES3013',
                'course_name' => 'Principle of Software Engineering',
                'next_course_code' => 'DES3023',
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['SDLC', 'Agile/Waterfall', 'UML', 'Requirements', 'Patterns', 'Testing', 'TDD', 'Evolution', 'Jira'],
                'course_content_outline' => ['Week 1-2: Intro & Models', 'Week 3: Process', 'Week 4: Req Eng 1', 'Week 5: Req Eng 2', 'Week 6: Modeling', 'Week 7: Behavioral 1', 'Week 8: Behavioral 2', 'Week 9: Architecture', 'Week 10: Design 1', 'Week 11: Design 2', 'Week 12: Testing', 'Week 13: Evolution', 'Week 14: PM'],
            ],
            [
                'course_code' => 'DES3073',
                'course_name' => 'Software Engineering Project',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Full-Stack Dev', 'Defensive Programming', 'DB & API', 'UI/UX', 'Agile PM', 'QA Metrics', 'Deployment', 'Documentation', 'Data/IoT'],
                'course_content_outline' => ['Week 1: Trends', 'Week 2: Planning', 'Week 3: SRS', 'Week 4: Architecture', 'Week 5: Prep', 'Week 6: Dev Basic', 'Week 7: Backend', 'Week 8: DB', 'Week 9: Frontend', 'Week 10: Security', 'Week 11: Unit Test', 'Week 12: UAT', 'Week 13: Migration', 'Week 14: Presentation'],
            ],
            [
                'course_code' => 'DES3053',
                'course_name' => 'Software Testing and Quality',
                'next_course_code' => 'DES3073',
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['SQA', 'Black-Box Testing', 'White-Box Testing', 'UAT', 'Regression Testing', 'API Testing', 'Test Design', 'Selenium/Postman', 'Static Analysis'],
                'course_content_outline' => ['Week 1: Intro', 'Week 2: Fundamentals', 'Week 3: Levels', 'Week 4-6: Black-Box', 'Week 7-8: White-Box', 'Week 9: Regression', 'Week 10: UAT', 'Week 11: API', 'Week 12: Execution I', 'Week 13: Execution II', 'Week 14: Static'],
            ],
            [
                'course_code' => 'DES3083',
                'course_name' => 'Software Engineering Process',
                'next_course_code' => null,
                'category' => 'FOCUS',
                'credit' => 3,
                'associated_skills' => ['SDLC/SPLC', 'Agile (XP, Kanban)', 'Plan-Driven', 'SPI', 'Assessment', 'Process Mining', 'Change Mgmt', 'Collaboration', 'Jira/DevOps'],
                'course_content_outline' => ['Week 1-2: Intro', 'Week 3: Models', 'Week 4: SDLC vs SPLC', 'Week 5: Plan-Driven', 'Week 6: Advanced', 'Week 7: Agility', 'Week 8: Scrum', 'Week 9: Extensions', 'Week 10: Metrics', 'Week 11: Trends', 'Week 12-13: Case Studies', 'Week 14: Synthesis'],
            ],
            [
                'course_code' => 'DES3103',
                'course_name' => 'Software Validation and Verification',
                'next_course_code' => null,
                'category' => 'FOCUS',
                'credit' => 3,
                'associated_skills' => ['V&V Strategies', 'SQA', 'IEEE Standards', 'HCI Testing', 'Maturity Models', 'Failure Analysis', 'Automation', 'Safety Risk', 'Postman', 'Jira'],
                'course_content_outline' => ['Week 1: Briefing', 'Week 2-3: Intro', 'Week 4: SQA', 'Week 5: Metrics', 'Week 6: Reviews', 'Week 7: QC', 'Week 8: HCI', 'Week 9: Manual/Auto', 'Week 10: Models', 'Week 11: Analysis', 'Week 12: Culture', 'Week 13: Safety', 'Week 14: Reflection'],
            ],
            [
                'course_code' => 'DES3113',
                'course_name' => 'Mobile Application Design & Development',
                'next_course_code' => null,
                'category' => 'FOCUS',
                'credit' => 3,
                'associated_skills' => ['Flutter & Dart', 'UI Architecture', 'State Mgmt', 'CRUD (Firebase/SQLite)', 'Navigation', 'Notifications', 'Interactions', 'Deployment', 'Defensive Programming'],
                'course_content_outline' => ['Week 1-2: Intro', 'Week 2: Flutter', 'Week 3: UI Arch', 'Week 4: State', 'Week 5: Layouts', 'Week 6: Inputs', 'Week 7: Nav', 'Week 8: Notifications', 'Week 9: Persistence', 'Week 10: Interactions', 'Week 11: Advanced Nav', 'Week 12: Custom UI', 'Week 13: CRUD', 'Week 14: Deployment'],
            ],
            [
                'course_code' => 'DTN3023',
                'course_name' => 'Computer Networks',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Network Admin', 'Packet Tracer', 'TCP/IP', 'OSI Model', 'Routing', 'LAN/WAN', 'Security', 'Wireshark', 'IPv4'],
                'course_content_outline' => ['Week 1-2: Intro', 'Week 3-4: Application', 'Week 5-7: Transport', 'Week 8-11: Network', 'Week 12-14: Data Link'],
            ],
            [
                'course_code' => 'DTN3043',
                'course_name' => 'Operating Systems',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Process Mgmt', 'Memory Mgmt', 'File Systems', 'RAID', 'Security', 'Deadlock', 'Bash', 'PowerShell', 'Virtualization'],
                'course_content_outline' => ['Week 1-2: Overview', 'Week 3-4: Structures', 'Week 5-6: Processes', 'Week 7: Coordination', 'Week 8-9: Memory', 'Week 10: Storage', 'Week 11: I/O', 'Week 12: Protection', 'Week 13: Windows', 'Week 14: Future'],
            ],
            [
                'course_code' => 'DTS3013',
                'course_name' => 'Structured Programming',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['C++', 'Logic', 'Algorithms', 'Debugging', 'Pointers', 'File I/O', 'Data Structures', 'VS Code', 'Modular Prog'],
                'course_content_outline' => ['Week 1: Intro', 'Week 2: C++ Basics', 'Week 3-4: Control', 'Week 5-6: Loops', 'Week 7-8: Functions', 'Week 9: Pointers', 'Week 10-11: Arrays', 'Week 12: Files', 'Week 13: Structs', 'Week 14: Review'],
            ],
            [
                'course_code' => 'DTS3093',
                'course_name' => 'Object Oriented Programming',
                'next_course_code' => null,
                'category' => 'MAJOR',
                'credit' => 3,
                'associated_skills' => ['Java', 'OOD', 'Encapsulation/Inheritance/Polymorphism', 'Abstraction', 'UML', 'IntelliJ', 'GUI', 'Collections', 'Clean Code'],
                'course_content_outline' => ['Week 1: Intro', 'Week 2: Principles', 'Week 3: Java Intro', 'Week 4: Programs', 'Week 5: Identifiers', 'Week 6: Variables', 'Week 7: Classes', 'Week 8: Predefined', 'Week 9: Wrappers', 'Week 10: Static', 'Week 11: Arrays', 'Week 12: Objects', 'Week 13: Inheritance', 'Week 14: Polymorphism'],
            ],
        ];

        // START TRANSACTION
        DB::beginTransaction();
        try {
            foreach ($courses as $data) {
                Course::updateOrCreate(
                    ['course_code' => $data['course_code']], // Lookup Key
                    [
                        'course_name' => $data['course_name'],
                        'next_course_code' => $data['next_course_code'],
                        'category' => $data['category'],
                        'credit' => $data['credit'],
                        'associated_skills' => $data['associated_skills'], // Model cast handles array->json
                        'course_content_outline' => $data['course_content_outline'],
                    ]
                );
            }
            DB::commit();
            $this->info('Successfully synced '.count($courses).' courses!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Sync failed: '.$e->getMessage());
        }
    }
}
