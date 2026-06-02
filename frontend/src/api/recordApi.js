import axios from "axios";

const API =
  "http://localhost:5000/api/records";

// Get All Records
export const fetchRecords =
  async () => {
    const response =
      await axios.get(API);

    return response.data.data;
  };

// Get Single Record
export const fetchRecordById =
  async (id) => {
    const response =
      await axios.get(
        `${API}/${id}`
      );

    return response.data.data;
  };

// Add Record
export const createRecord =
  async (recordData) => {
    const response =
      await axios.post(
        API,
        recordData
      );

    return response.data;
  };

// Update Record
export const updateRecordApi =
  async (
    id,
    updateData
  ) => {
    const response =
      await axios.put(
        `${API}/${id}`,
        updateData
      );

    return response.data;
  };