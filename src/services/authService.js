import API from "../config/api";

// signup api
export const signupUser = (data) => {
    return API.post("/auth.php?action=signup", data);
};

// login api
export const loginUser = (data) => {
    return API.post("/auth.php?action=login", data);
};