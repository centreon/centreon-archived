import { ReactNode } from 'react';

import { useTranslation } from 'react-i18next';

import { Dialog } from '@centreon/ui';

import { labelCancel, labelMenageEnvelope } from '../../../translatedLabels';

interface Props {
  children: ReactNode;
  labelConfirm: string;
  open: boolean;
  sendCancel: (value: boolean) => void;
  sendConfirm: (value: boolean) => void;
  setOpen: (value: boolean) => void;
}

const AnomalyDetectionModalConfirmation = ({
  open,
  setOpen,
  sendCancel,
  sendConfirm,
  children,
  labelConfirm,
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const cancel = (): void => {
    sendCancel(true);
    setOpen(false);
  };

  return (
    <Dialog
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelConfirm)}
      labelTitle={t(labelMenageEnvelope)}
      open={open}
      onCancel={cancel}
      onConfirm={(): void => sendConfirm(true)}
    >
      {children}
    </Dialog>
  );
};
export default AnomalyDetectionModalConfirmation;
