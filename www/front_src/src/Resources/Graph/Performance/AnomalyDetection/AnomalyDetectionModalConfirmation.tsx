import { useTranslation } from 'react-i18next';

import { Typography } from '@mui/material';

import { Dialog } from '@centreon/ui';

import {
  labelEditAnomalyDetectionConfirmation,
  labelMenageEnvelope,
  labelSave,
  labelCancel,
} from '../../../translatedLabels';

interface Props {
  dataTestid: string;
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
  dataTestid,
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const cancel = (): void => {
    sendCancel(true);
    setOpen(false);
  };

  return (
    <Dialog
      data-testid={dataTestid}
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelSave)}
      labelTitle={t(labelMenageEnvelope)}
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
