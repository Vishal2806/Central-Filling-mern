const StatusBadge = ({ status }) => {
  const styles = {
    APPROVED:
      "bg-green-100 text-green-700",

    RETURNED:
      "bg-red-100 text-red-700",

    PENDING:
      "bg-yellow-100 text-yellow-700",

    RESUBMITTED:
      "bg-blue-100 text-blue-700",

    SUBMITTED:
      "bg-gray-100 text-gray-700",
  };

  return (
    <span
      className={`px-3 py-1 rounded-full text-xs font-semibold ${
        styles[status] ||
        "bg-gray-100 text-gray-700"
      }`}
    >
      {status}
    </span>
  );
};

export default StatusBadge;