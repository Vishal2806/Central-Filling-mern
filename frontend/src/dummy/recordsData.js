const recordsData = [
  {
    id: 1,
    filingNo: "FIL/2026/101",
    caseNo: "CF/2026/458",
    advocateName: "Rajesh Sharma",
    status: "APPROVED",
    totalReturns: 2,
    latestRemark: "Paper Book Done OK",
    history: [
      {
        status: "SUBMITTED",
        remark: "Initial Submission",
        date: "25 May 2026",
      },
      {
        status: "RETURNED",
        remark: "Missing affidavit",
        date: "26 May 2026",
      },
      {
        status: "RESUBMITTED",
        remark: "Corrected documents submitted",
        date: "28 May 2026",
      },
      {
        status: "RETURNED",
        remark: "Wrong signature",
        date: "29 May 2026",
      },
      {
        status: "APPROVED",
        remark: "Verified Successfully",
        date: "31 May 2026",
      },
    ],
  },

  {
    id: 2,
    filingNo: "FIL/2026/102",
    caseNo: "CF/2026/459",
    advocateName: "Amit Verma",
    status: "PENDING",
    totalReturns: 1,
    latestRemark: "Under Review",
    history: [
      {
        status: "SUBMITTED",
        remark: "Initial Submission",
        date: "25 May 2026",
      },
      {
        status: "RETURNED",
        remark: "Incomplete pagination",
        date: "26 May 2026",
      },
      {
        status: "RESUBMITTED",
        remark: "Corrected pagination",
        date: "27 May 2026",
      },
    ],
  },
];

export default recordsData;