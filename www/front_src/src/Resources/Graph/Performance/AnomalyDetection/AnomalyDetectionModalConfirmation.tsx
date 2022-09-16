import { Typography } from '@mui/material';

import { Dialog } from '@centreon/ui';

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
  const cancel = (): void => {
    sendCancel(true);
    setOpen(false);
  };

  return (
    <Dialog
      labelTitle="Menage envelope size"
      open={open}
      onCancel={cancel}
      onClose={(): void => setOpen(false)}
      onConfirm={(): void => sendConfirm(true)}
    >
      <Typography>
        Are you sure you want to change the size of the envelope? The new
        envelope size will be applied immediately.
      </Typography>
    </Dialog>
  );
};
export default AnomalyDetectionModalConfirmation;
