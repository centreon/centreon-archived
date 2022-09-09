export enum ServerType {
  Base = 'base',
  Poller = 'poller',
  Remote = 'remote',
}

export enum WizardButtonsTypes {
  'Next',
  'Apply',
}

export interface WizardFormProps {
  changeServerType: (serverType: ServerType) => void;
  goToNextStep: () => void;
  goToPreviousStep: () => void;
}

export interface Props {
  goToNextStep: () => void;
  goToPreviousStep: () => void;
}

export interface WaitList {
  id: string;
  ip: string;
  server_name: string;
}

export interface PollerRemoteList {
  id: string;
  ip: string;
  name: string;
}

export interface Poller {
  id: string;
  text: string;
}
