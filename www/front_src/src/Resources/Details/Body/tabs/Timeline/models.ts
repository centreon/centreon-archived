export interface TimelineEventObject {
  output?: string;
  create_time: string;
  tries?: string;
  status?: string;
  type?: string;
  severity_code?: number;
  notification_contact?: string;
}

export interface TimelineEvent {
  type: string;
  id: string;
  date: string;
  object: TimelineEventObject;
}
