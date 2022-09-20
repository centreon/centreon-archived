import { useTranslation } from 'react-i18next';

import { Typography } from '@mui/material';

import { Dialog } from '@centreon/ui';

import {
  labelEditAnomalyDetectionConfirmation,
  LabelMenageEnvelope,
} from '../../../translatedLabels';

interface Props {
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
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const cancel = (): void => {
    sendCancel(true);
    setOpen(false);
  };

  return (
    <Dialog
      labelTitle={t(LabelMenageEnvelope)}
      open={open}
      onCancel={cancel}
      onClose={(): void => setOpen(false)}
      onConfirm={(): void => sendConfirm(true)}
    >
      <Typography>{t(labelEditAnomalyDetectionConfirmation)}</Typography>
    </Dialog>
  );
};
export default AnomalyDetectionModalConfirmation;
