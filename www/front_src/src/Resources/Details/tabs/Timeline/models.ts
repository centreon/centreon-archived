export interface Status {
  severityCode: number;
  name: string;
}

export interface TimelineEvent {
  type: string;
  id: number;
  date: string;
  startDate?: string;
  endDate?: string;
  content: string;
  status?: Status;
  tries?: string;
  authorName?: string;
}
