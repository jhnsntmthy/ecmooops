/* Tin Can configuration */

//
// ActivityID that is sent for the statement's object
//
TC_COURSE_ID = "http://propel.scitent.us/xapi-packages/SexualHarassmentScitent";

//
// CourseName for the activity
//
TC_COURSE_NAME = {
    "en-US": "Sexual Harassment Course"
};

//
// CourseDesc for the activity
//
TC_COURSE_DESC = {
    "en-US": "Sexual Harassment Course"
};

//
// Pre-configured LRSes that should receive data, added to what is included
// in the URL and/or passed to the constructor function.
//
// An array of objects where each object may have the following properties:
//
//    endpoint: (including trailing slash '/')
//    auth:
//    allowFail: (boolean, default true)
//
TC_RECORD_STORES = [
    // {
    //     endpoint: "https://cloud.scorm.com/ScormEngineInterface/TCAPI/public/",
    //     auth:     "Basic VGVzdFVzZXI6cGFzc3dvcmQ="
    // }
];
