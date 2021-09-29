export enum Status {
  error = 0,
  ok = 1,
}

export interface StatusMessage {
  message: string | null;
  status: number;
}
