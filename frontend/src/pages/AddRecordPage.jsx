import { useState } from "react";
import { useNavigate } from "react-router-dom";
import MainLayout from "../layouts/MainLayout";
import PageHeader from "../components/common/PageHeader";
import { useRecords } from "../context/RecordsContext";

const AddRecordPage = () => {
  const navigate = useNavigate();
  const { addRecord } = useRecords();

  const [formData, setFormData] = useState({
    caseNature: "Civil",
    caseTypeCode: "",
    caseNo: "",
    caseYear: "",       
    advocateName: "",    
    advocateContact: "", 
    paperbookSets: "1",  // Added numeric string initial value state tracker
    status: "SUBMITTED",
    remark: "",
  });

  const civilAndWritOptions = [
    "ARBA", "ARBAP", "ARBR", "AW", "CA", "CEA", "CER", "CESR", "COMA", "COMP", 
    "CONC", "CONT", "CONTS", "CP", "CR", "CS", "CVLREF", "EA", "EDR", "EP", 
    "FA", "FAM", "FA(MAT)", "ITA", "ITR", "LPA", "MA", "MAC", "MCA", "MCC", 
    "MCCS", "MCP", "MP", "MWP", "OD", "REVP", "SA", "STR", "TAXC", "TPC", 
    "WA", "WP", "WP227", "WPC", "WPCR", "WPHC", "WPL", "WPPIL", "WPS", "WPT", "WTA", "WTR"
  ];

  const criminalOptions = [
    "ACQA", "CONTR", "CRA", "CRMP", "CRREA", "MCRC", "MCRCA", "MCRP", "TPCR"
  ];

  const currentCaseTypeOptions = 
    formData.caseNature === "Criminal" ? criminalOptions : civilAndWritOptions;

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleNatureChange = (natureType) => {
    setFormData({
      ...formData,
      caseNature: natureType,
      caseTypeCode: ""
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const success = await addRecord(formData);
    if (success) {
      navigate("/records");
    }
  };

  return (
    <MainLayout>
      <PageHeader
        title="Add Record"
        subtitle="Create new automated filing registry entry"
      />

      <div className="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mt-6">
        
        {/* ================= CASE DETAILS SECTION ================= */}
        <h2 className="text-xl font-bold text-[#1f3a56] mb-4">
          Case Details
        </h2>
        
        <div className="bg-slate-50 p-5 rounded-xl border border-slate-200 mb-8 flex flex-col gap-5">
          
          {/* Nature Buttons */}
          <div>
            <label className="block mb-2 text-sm font-semibold text-gray-600">
              Case Nature <span className="text-red-500">*</span>
            </label>
            <div className="flex bg-white p-1 rounded-xl border border-gray-300 w-fit">
              {["Civil", "Criminal", "Writ"].map((nature) => (
                <button
                  key={nature}
                  type="button"
                  onClick={() => handleNatureChange(nature)}
                  className={`px-5 py-2.5 rounded-lg text-sm font-bold transition-all ${
                    formData.caseNature === nature
                      ? "bg-[#1f3a56] text-white shadow-sm"
                      : "text-gray-500 hover:text-gray-800 hover:bg-gray-100"
                  }`}
                >
                  {nature}
                </button>
              ))}
            </div>
          </div>

          {/* Dynamic Dropdown Row */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">
            
            {/* Case Type */}
            <div className="md:col-span-2">
              <label className="block mb-2 text-sm font-semibold text-gray-600">
                Case Type <span className="text-red-500">*</span>
              </label>
              <select
                name="caseTypeCode"
                value={formData.caseTypeCode}
                onChange={handleChange}
                required
                className="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
              >
                <option value="">— Select Case Type —</option>
                {currentCaseTypeOptions.map((option) => (
                  <option key={option} value={option}>
                    {option}
                  </option>
                ))}
              </select>
            </div>

            {/* Case No */}
            <div>
              <label className="block mb-2 text-sm font-semibold text-gray-600">
                Case No <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="caseNo"
                value={formData.caseNo}
                onChange={handleChange}
                placeholder="Case No"
                required
                maxLength="7"
                className="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
              />
            </div>

            {/* Year */}
            <div>
              <label className="block mb-2 text-sm font-semibold text-gray-600">
                Year <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="caseYear"
                value={formData.caseYear}
                onChange={handleChange}
                placeholder="Enter Year"
                required
                maxLength="4"
                className="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
              />
            </div>

          </div>
        </div>


        {/* ================= FILING INFORMATION SECTION ================= */}
        <h2 className="text-xl font-bold text-[#1f3a56] mb-6">
          Filing Information
        </h2>

        <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-5">
          
          {/* Advocate Name */}
          <div>
            <label className="block mb-2 text-sm font-semibold text-gray-600">
              Advocate Name <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              name="advocateName"
              value={formData.advocateName}
              onChange={handleChange}
              placeholder="Enter Advocate Name"
              required
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
            />
          </div>

          {/* Contact Number */}
          <div>
            <label className="block mb-2 text-sm font-semibold text-gray-600">
              Contact Number <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              name="advocateContact"
              value={formData.advocateContact}
              onChange={handleChange}
              placeholder="Enter Advocate Contact Number"
              required
              maxLength="15"
              pattern="[0-9+ ]*"
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
            />
          </div>

          {/* NEW FIELD: Paperbook Set Numeric Input Area */}
          <div>
            <label className="block mb-2 text-sm font-semibold text-gray-600">
              Paperbook Set (Count) <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              name="paperbookSets"
              value={formData.paperbookSets}
              onChange={handleChange}
              placeholder="1"
              min="1"
              max="99"
              required
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
            />
          </div>

          {/* Status */}
          <div>
            <label className="block mb-2 text-sm font-semibold text-gray-600">
              Status
            </label>
            <select
              name="status"
              value={formData.status}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
            >
              <option value="SUBMITTED">Submitted</option>
              <option value="PENDING">Pending</option>
              <option value="RETURNED">Returned</option>
              <option value="RESUBMITTED">Resubmitted</option>
              <option value="APPROVED">Approved</option>
            </select>
          </div>

          {/* Remark */}
          <div className="md:col-span-2">
            <label className="block mb-2 text-sm font-semibold text-gray-600">
              Remark <span className="text-red-500">*</span>
            </label>
            <textarea
              rows="2"
              name="remark"
              value={formData.remark}
              onChange={handleChange}
              placeholder="Enter Remarks"
              required
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
            />
          </div>

          {/* Save Button */}
          <div className="md:col-span-2 mt-2">
            <button
              type="submit"
              className="bg-[#1f3a56] hover:bg-[#163047] text-white px-6 py-3 rounded-xl font-semibold transition"
            >
              Save Record
            </button>
          </div>
        </form>
      </div>
    </MainLayout>
  );
};

export default AddRecordPage;