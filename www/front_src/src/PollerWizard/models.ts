export enum ServerType {
  Base = 'base',
  Poller = 'poller',
  Remote = 'remote',
}

export interface WizardFormProps {
  changeServerType: (serverType: ServerType) => void;
  goToNextStep: () => void;
  goToPreviousStep: () => void;
}
