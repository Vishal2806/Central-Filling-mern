import { useNavigate } from "react-router-dom";
import StatusBadge from "../common/StatusBadge";

const RecordsTable = ({ records }) => {
  const navigate = useNavigate();

  // Helper utility function to parse 24h standard backend strings ("15:30") to clean 12h AM/PM format
  const formatTimeToAMPM = (timeStr) => {
    if (!timeStr) return "N/A";
    const [hours, minutes] = timeStr.split(":");
    let hh = parseInt(hours, 10);
    const ampm = hh >= 12 ? "PM" : "AM";
    hh = hh % 12 || 12; // Convert 0 to 12
    return `${hh}:${minutes} ${ampm}`;
  };

  return (
    <div className="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mt-6">
      {/* Header */}
      <div className="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-[#1f3a56]">
            Filing Records
          </h2>
          <p className="text-sm text-gray-500 mt-1">
            Complete registry filing entries
          </p>
        </div>

        <div className="bg-[#eef2f7] px-4 py-2 rounded-lg text-sm font-medium">
          Total Records: {records.length}
        </div>
      </div>

      {/* Table */}
      <div className="overflow-x-auto">
        <table className="min-w-[1200px] w-full">
          <thead className="bg-[#1f3a56] text-white">
            <tr>
              {/* Removed Case Info No. Column Header */}
              <th className="px-5 py-4 text-left">Case No / Year</th>
              <th className="px-5 py-4 text-left">Filing Date</th>
              <th className="px-5 py-4 text-left">Filing Time</th>
              <th className="px-5 py-4 text-left">Advocate</th>
              <th className="px-5 py-4 text-left">Contact Number</th>
              <th className="px-5 py-4 text-left">Status</th>
              <th className="px-5 py-4 text-left">Return Count</th>
              <th className="px-5 py-4 text-left">Latest Remark</th>
            </tr>
          </thead>

          <tbody>
            {records.map((record, index) => (
              <tr
                key={record.id || index}
                onClick={() => navigate(`/records/${record.id}`)}
                className="border-b border-gray-200 hover:bg-[#f8fafc] cursor-pointer transition"
              >
                {/* Combined Case Number and Case Year into a clean display layout */}
                <td className="px-5 py-4 font-semibold text-[#1f3a56]">
                  {record.caseNo || "—"} / {record.caseYear || "—"}
                  {record.caseTypeCode && (
                    <span className="text-xs text-gray-400 font-medium px-1.5 py-0.5 bg-gray-100 rounded ml-2">
                      {record.caseTypeCode}
                    </span>
                  )}
                </td>

                {/* Localized Date Format (DD/MM/YYYY) */}
                <td className="px-5 py-4 text-gray-600 font-medium">
                  {record.filingDate ? new Date(record.filingDate).toLocaleDateString('en-GB') : "N/A"}
                </td>

                {/* Parsed AM/PM time */}
                <td className="px-5 py-4 text-gray-600 font-medium">
                  {formatTimeToAMPM(record.filingTime)}
                </td>

                <td className="px-5 py-4 text-gray-800 font-medium">
                  {record.advocateName || "—"}
                </td>

                {/* Added Advocate Contact Column Cell */}
                <td className="px-5 py-4 text-gray-600 font-medium">
                  {record.advocateContact || "—"}
                </td>

                <td className="px-5 py-4">
                  <StatusBadge status={record.status} />
                </td>

                <td className="px-5 py-4">
                  <span className="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                    {record.totalReturns ?? 0}
                  </span>
                </td>

                <td className="px-5 py-4 max-w-xs truncate text-gray-500 italic">
                  {record.latestRemark || "—"}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default RecordsTable;