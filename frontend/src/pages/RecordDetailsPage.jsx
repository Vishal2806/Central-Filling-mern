import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import MainLayout from "../layouts/MainLayout";
import PageHeader from "../components/common/PageHeader";
import StatusBadge from "../components/common/StatusBadge";
import { fetchRecordById, updateRecordApi } from "../api/recordApi";

const RecordDetailsPage = () => {
  const { id } = useParams();
  const [record, setRecord] = useState(null);
  const [loading, setLoading] = useState(true);
  const [status, setStatus] = useState("RETURNED");
  const [remark, setRemark] = useState("");

  // Helper utility function to parse 24h standard backend strings to clean 12h AM/PM text
  const formatTimeToAMPM = (timeStr) => {
    if (!timeStr) return "";
    const [hours, minutes] = timeStr.split(":");
    let hh = parseInt(hours, 10);
    const ampm = hh >= 12 ? "PM" : "AM";
    hh = hh % 12 || 12; 
    return `${hh}:${minutes} ${ampm}`;
  };

  // Load Record
  useEffect(() => {
    loadRecord();
  }, [id]);

  const loadRecord = async () => {
    try {
      const data = await fetchRecordById(id);
      setRecord(data);
    } catch (error) {
      console.log(error);
    } finally {
      setLoading(false);
    }
  };

  // Update Record
  const handleUpdate = async (e) => {
    e.preventDefault();
    if (!remark.trim()) return;

    try {
      await updateRecordApi(id, {
        status,
        remark,
      });
      setRemark("");
      loadRecord();
    } catch (error) {
      console.log(error);
      alert("Update failed");
    }
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="text-xl p-10 text-center font-semibold">Loading...</div>
      </MainLayout>
    );
  }

  if (!record) {
    return (
      <MainLayout>
        <div className="text-red-500 text-xl p-10 text-center font-semibold">Record not found</div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <PageHeader
        title="Record Details"
        subtitle="Complete filing history and status tracking"
      />

      {/* Main Details */}
      <div className="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mt-6">
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 border-b border-gray-100 pb-6">
          
          {/* Column 1: Case Type Classification Summary */}
          <div>
            <p className="text-xs font-bold text-gray-400 uppercase tracking-wider">Case Details</p>
            <h3 className="text-lg font-extrabold text-[#1f3a56] mt-2">
              {record.caseNature} <span className="text-xs text-gray-400 font-medium px-1.5 py-0.5 bg-gray-100 rounded ml-1">{record.caseTypeCode}</span>
            </h3>
          </div>

          {/* Column 2: Case Number */}
          <div>
            <p className="text-xs font-bold text-gray-400 uppercase tracking-wider">Case No</p>
            <h3 className="text-lg font-bold text-[#1f3a56] mt-2">
              {record.caseNo || "—"}
            </h3>
          </div>

          {/* Column 3: Case Year Input Entry */}
          <div>
            <p className="text-xs font-bold text-gray-400 uppercase tracking-wider">Year</p>
            <h3 className="text-lg font-bold text-[#1f3a56] mt-2">
              {record.caseYear || "—"}
            </h3>
          </div>

          {/* Column 4: Advocate Name */}
          <div>
            <p className="text-xs font-bold text-gray-400 uppercase tracking-wider">Advocate Name</p>
            <h3 className="text-lg font-bold text-[#1f3a56] mt-2">
              {record.advocateName || "—"}
            </h3>
          </div>

          {/* Column 5: Status Badges */}
          <div>
            <p className="text-xs font-bold text-gray-400 uppercase tracking-wider">Current Status</p>
            <div className="mt-1.5">
              <StatusBadge status={record.status} />
            </div>
          </div>
        </div>

        {/* Dynamic Meta Bar Row tracking contact details, automated database stamps, and Paperbook Sets */}
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 pt-6 text-sm text-gray-500 font-medium">
          <div>
            <span className="text-gray-400 mr-1.5">Contact Number:</span> 
            <span className="text-slate-700 font-bold">{record.advocateContact || "N/A"}</span>
          </div>
          <div>
            <span className="text-gray-400 mr-1.5">Filing Date:</span> 
            <span className="text-slate-700 font-bold">
              {record.filingDate ? new Date(record.filingDate).toLocaleDateString('en-GB') : "N/A"}
            </span>
          </div>
          <div>
            <span className="text-gray-400 mr-1.5">Filing Time:</span> 
            <span className="text-slate-700 font-bold">{formatTimeToAMPM(record.filingTime) || "N/A"}</span>
          </div>
          {/* UPDATED: Added Paperbook Sets info box display mapping */}
          <div>
            <span className="text-gray-400 mr-1.5">Paperbook Sets:</span> 
            <span className="text-slate-700 font-bold bg-slate-100 px-2.5 py-1 rounded-md text-xs">
              {record.paperbookSets ?? "—"} Set(s)
            </span>
          </div>
        </div>

        {/* Summary Metric Stats Panel layout boxes */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mt-8">
          <div className="bg-[#f8fafc] border border-gray-200 rounded-xl p-5">
            <p className="text-gray-500 text-sm font-semibold">Total Returns</p>
            <h2 className="text-3xl font-extrabold text-red-600 mt-2">
              {record.totalReturns}
            </h2>
          </div>

          <div className="bg-[#f8fafc] border border-gray-200 rounded-xl p-5">
            <p className="text-gray-500 text-sm font-semibold">Latest Remark</p>
            <h2 className="text-base font-bold text-[#1f3a56] mt-2 italic">
              "{record.latestRemark || "No remark assigned."}"
            </h2>
          </div>

          <div className="bg-[#f8fafc] border border-gray-200 rounded-xl p-5">
            <p className="text-gray-500 text-sm font-semibold">History Entries</p>
            <h2 className="text-3xl font-extrabold text-[#1f3a56] mt-2">
              {record.history?.length || 0}
            </h2>
          </div>
        </div>
      </div>

      {/* Update Form */}
      <div className="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mt-6">
        <h2 className="text-2xl font-bold text-[#1f3a56]">
          Update Filing Status
        </h2>

        <form onSubmit={handleUpdate} className="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
          <div>
            <label className="block mb-2 text-sm font-semibold text-gray-600">
              Status
            </label>
            <select
              value={status}
              onChange={(e) => setStatus(e.target.value)}
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56] bg-white"
            >
              <option value="RETURNED">Returned</option>
              <option value="RESUBMITTED">Resubmitted</option>
              <option value="APPROVED">Approved</option>
              <option value="PENDING">Pending</option>
            </select>
          </div>

          <div>
            <label className="block mb-2 text-sm font-semibold text-gray-600">
              Remark
            </label>
            <input
              type="text"
              value={remark}
              onChange={(e) => setRemark(e.target.value)}
              placeholder="Enter update remark"
              className="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#1f3a56]"
            />
          </div>

          <div className="md:col-span-2">
            <button
              type="submit"
              className="bg-[#1f3a56] hover:bg-[#163047] text-white px-6 py-3 rounded-xl font-semibold transition"
            >
              Save Update
            </button>
          </div>
        </form>
      </div>

      {/* Timeline Tracking Section */}
      <div className="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mt-6">
        <h2 className="text-2xl font-bold text-[#1f3a56]">
          Filing Timeline History
        </h2>

        <div className="mt-8 relative border-l-2 border-[#1f3a56] ml-4">
          {record.history?.map((historyItem) => (
            <div key={historyItem.id} className="mb-10 ml-8 relative">
              <div className="absolute -left-[42px] top-1 h-5 w-5 rounded-full bg-[#1f3a56] border-4 border-white shadow"></div>

              <div className="bg-[#f8fafc] border border-gray-200 rounded-xl p-5">
                <div className="flex items-center justify-between flex-wrap gap-3">
                  <StatusBadge status={historyItem.status} />
                  <p className="text-sm text-gray-500 font-medium">
                    {historyItem.date ? new Date(historyItem.date).toLocaleString() : "N/A"}
                  </p>
                </div>
                <p className="mt-4 text-gray-700 font-medium">
                  {historyItem.remark}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </MainLayout>
  );
};

export default RecordDetailsPage;