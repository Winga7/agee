# Diagramme UML

Voici le diagramme de classe de notre projet

```mermaid
classDiagram
class Module {
+id: bigint
+title: string
+code: string
+description: text
+professor_id: bigint
+timestamps()
+professor()
+evaluations()
+classes()
+courseEnrollments()
}

    class Professor {
      +id: bigint
      +first_name: string
      +last_name: string
      +email: string
      +school_email: string
      +telephone: string
      +adress: string
      +birth_date: date
      +timestamps()
      +modules()
    }

    class Student {
      +id: bigint
      +first_name: string
      +last_name: string
      +email: string
      +school_email: string
      +telephone: string
      +birth_date: date
      +student_id: string
      +academic_year: string
      +status: string
      +timestamps()
      +classes()
      +courseEnrollments()
      +evaluationTokens()
    }

    class ClassGroup {
      +id: bigint
      +name: string
      +timestamps()
      +students()
      +modules()
    }

    class CourseEnrollment {
      +id: bigint
      +student_id: bigint
      +module_id: bigint
      +class_id: bigint
      +start_date: date
      +end_date: date
      +timestamps()
      +student()
      +module()
      +class()
    }

    class EvaluationToken {
      +id: bigint
      +token: string
      +module_id: bigint
      +student_email: string
      +class_id: bigint
      +form_id: bigint
      +expires_at: datetime
      +is_used: boolean
      +used_at: timestamp
      +timestamps()
      +module()
      +class()
      +form()
      +evaluations()
      +generateToken()
      +isExpired()
      +isValid()
      +getStatus()
    }

    %% Relations entre les classes
    Module "1" -- "0..*" CourseEnrollment : contient
    Module "1" -- "0..*" EvaluationToken : génère
    Module "1" -- "1" Professor : est enseigné par
    Module "0..*" -- "0..*" ClassGroup : lié à

    Student "1" -- "0..*" CourseEnrollment : inscrit via
    Student "0..*" -- "0..*" ClassGroup : appartient à
    Student "1" -- "0..*" EvaluationToken : reçoit

    ClassGroup "1" -- "0..*" CourseEnrollment : gère
    ClassGroup "1" -- "0..*" EvaluationToken : attribue

    %% Relations complémentaires basées sur votre description
    %% Un Module peut avoir plusieurs évaluations (méthode evaluations())
    %% Un Student peut suivre plusieurs classes (méthode classes())
    %% Une Inscription de Cours (CourseEnrollment) lie un Student à un Module dans une ClassGroup
    %% Un EvaluationToken est lié à un Module, à une ClassGroup et à un Student (via student_email)
