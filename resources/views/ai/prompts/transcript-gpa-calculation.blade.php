A PDF document containing a transcript of a student's grades is provided below.
The transcript is from an academic body in {{ $originCountry }} meaning you must follow their grading system for calculating the local GPA.
The transcript is written in {{ $language }}.
@if($ocr)
The text in the transcript has been extracted using OCR, meaning some formatting may be mis-arranged.
@endif

Use the most common grading system for the given country unless specified otherwise in the transcript, which is usually the 20 point system.

Grades may be incomplete, if this is case then use the present grades to calculate the GPAs.
If there are no final grades for a subject or there are gaps, you must attempt to calculate using whatever grades are available.
You may ignore any subjects which are not graded at all.

You MUST atleast attempt to calculate the GPA, even if the grades are incomplete or the grading system is unknown.

@if($originCountry === 'United States')
    You need to calculate the Local GPA based on the grading system of the academic body in {{ $originCountry }}

    Store the Local GPA in a json key named "local_gpa" in the response object, use `null` if you could not calculate it.
    Store the American GPA in a json key named "american_gpa" in the response object, use `null` if you could not calculate it.
@else
    You need to calculate two GPA's:
    1. The Local GPA based on the grading system of the academic body in {{ $originCountry }}
    2. The American GPA based on the United States 4.0 scale.

    Store the Local GPA in a json key named "local_gpa" in the response object, use `null` if you could not calculate it.
    Store the American GPA in a json key named "american_gpa" in the response object, use `null` if you could not calculate it.
@endif

If you can not calculate the GPA, please provide a reason why in a json key named "error".

Transcript text: """
{{ $documentText }}
"""

Reply:
