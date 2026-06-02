import {
  createContext,
  useContext,
  useEffect,
  useState,
} from "react";

import {
  fetchRecords,
  createRecord,
} from "../api/recordApi";

const RecordsContext = createContext();

export const RecordsProvider = ({ children }) => {
  const [records, setRecords] = useState([]);
  const [loading, setLoading] = useState(true);

  // Load Records From Backend on initial mount
  useEffect(() => {
    loadRecords();
  }, []);

  const loadRecords = async () => {
    try {
      setLoading(true);
      const data = await fetchRecords();
      setRecords(data);
    } catch (error) {
      console.error("Failed to load records database:", error);
    } finally {
      setLoading(false);
    }
  };

  /**
   * Add Record Handler
   * Receives the complete form state payload object from AddRecordPage.jsx
   * (including caseNature, caseTypeCode, caseInfoNo, etc.)
   */
  const addRecord = async (newRecord) => {
    try {
      const response = await createRecord(newRecord);

      if (response.success) {
        // Refresh local memory state array by pulling updated records
        await loadRecords();
        return true;
      }
      
      return false;
    } catch (error) {
      console.error("API error while generating record entry:", error);

      alert(
        error.response?.data?.message ||
          "Error adding record entry to the database grid."
      );

      return false;
    }
  };

  return (
    <RecordsContext.Provider
      value={{
        records,
        loading,
        addRecord,
        refreshRecords: loadRecords // Exposed in case any component wants to force trigger a manual reload
      }}
    >
      {children}
    </RecordsContext.Provider>
  );
};

export const useRecords = () => useContext(RecordsContext);