export type ErrorCode =
  | "UNAUTHENTICATED" | "FORBIDDEN" | "NOT_FOUND" | "VALIDATION_ERROR"
  | "CONFLICT" | "RATE_LIMITED" | "NODE_OFFLINE" | "CAPACITY_EXCEEDED"
  | "RUNTIME_ERROR" | "STORAGE_ERROR" | "INTERNAL_ERROR";

export class ApiError extends Error {
  readonly status: number;
  readonly code: ErrorCode;
  readonly details?: unknown;

  constructor(code: ErrorCode, message: string, status = 500, details?: unknown) {
    super(message);
    this.name = "ApiError";
    this.code = code;
    this.status = status;
    this.details = details;
  }
}

export const errors = {
  unauthenticated: (message = "Authentication required") => new ApiError("UNAUTHENTICATED", message, 401),
  forbidden: (message = "You do not have permission to perform this action") => new ApiError("FORBIDDEN", message, 403),
  notFound: (message = "Resource not found") => new ApiError("NOT_FOUND", message, 404),
  validation: (message: string, details?: unknown) => new ApiError("VALIDATION_ERROR", message, 422, details),
  conflict: (message: string) => new ApiError("CONFLICT", message, 409),
  rateLimited: (message = "Too many requests") => new ApiError("RATE_LIMITED", message, 429),
  nodeOffline: () => new ApiError("NODE_OFFLINE", "The target node is offline", 503),
  capacity: (message = "The node does not have enough available capacity") => new ApiError("CAPACITY_EXCEEDED", message, 409),
  runtime: (message: string, details?: unknown) => new ApiError("RUNTIME_ERROR", message, 502, details),
  storage: (message: string) => new ApiError("STORAGE_ERROR", message, 503),
};

export function sendError(res: any, error: unknown, requestId: string) {
  const e = error instanceof ApiError ? error : new ApiError("INTERNAL_ERROR", "Internal server error", 500);
  if (!(error instanceof ApiError)) console.error({ requestId, error });
  return res.status(e.status).json({
    success: false,
    error: { code: e.code, message: e.message, ...(e.details === undefined ? {} : { details: e.details }) },
    requestId,
  });
}
