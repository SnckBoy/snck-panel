import React, { createContext, useContext, useState, useEffect, useCallback } from "react";
import axios from "axios";

export const AuthContext = createContext<any>(null);

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
  const [user, setUser] = useState<any>(null);
  const [token, setToken] = useState<string | null>(localStorage.getItem("shironex_token"));
  const [loading, setLoading] = useState(true);
  const [setupRequired, setSetupRequired] = useState<boolean | null>(null);

  const refreshSetupStatus = useCallback(async () => {
    try {
      const response = await axios.get("/api/auth/setup-status", { timeout: 8000 });
      setSetupRequired(response.data.setupRequired === true);
      return response.data.setupRequired === true;
    } catch {
      setSetupRequired(false);
      return false;
    }
  }, []);

  useEffect(() => {
    void refreshSetupStatus();

    if (!token) {
      setLoading(false);
      return;
    }

    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    axios.get("/api/auth/me", { timeout: 10000 }).then((response) => {
      setUser(response.data.user);
      setLoading(false);
    }).catch(() => {
      setToken(null);
      localStorage.removeItem("shironex_token");
      delete axios.defaults.headers.common["Authorization"];
      setUser(null);
      setLoading(false);
    });
  }, [token]);

  useEffect(() => {
    const interceptor = axios.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          setToken(null);
          setUser(null);
          localStorage.removeItem("shironex_token");
          delete axios.defaults.headers.common["Authorization"];
        }
        return Promise.reject(error);
      }
    );
    return () => axios.interceptors.response.eject(interceptor);
  }, []);

  const login = (newToken: string, newUser: any) => {
    setToken(newToken);
    setUser(newUser);
    setSetupRequired(false);
    localStorage.setItem("shironex_token", newToken);
    axios.defaults.headers.common["Authorization"] = `Bearer ${newToken}`;
    // Same-tab localStorage writes never fire the browser "storage" event, so
    // anything that needs to react to login within this tab (e.g. opening an
    // authenticated socket.io connection) has no signal otherwise. Broadcast
    // a same-tab custom event other providers can subscribe to.
    window.dispatchEvent(new CustomEvent("shironex-auth-changed", { detail: { token: newToken } }));
  };

  const logout = () => {
    setToken(null);
    setUser(null);
    localStorage.removeItem("shironex_token");
    delete axios.defaults.headers.common["Authorization"];
    window.dispatchEvent(new CustomEvent("shironex-auth-changed", { detail: { token: null } }));
  };

  const markSetupComplete = () => setSetupRequired(false);

  const refreshUser = async () => {
    try {
      const response = await axios.get("/api/auth/me");
      setUser(response.data.user);
    } catch {
      // The response interceptor handles expired sessions.
    }
  };

  const updateUser = (updatedFields: any) => {
    setUser((previous: any) => (previous ? { ...previous, ...updatedFields } : previous));
  };

  return (
    <AuthContext.Provider value={{
      user,
      token,
      login,
      logout,
      loading,
      setupRequired,
      refreshSetupStatus,
      markSetupComplete,
      refreshUser,
      updateUser,
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
