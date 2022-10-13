import { Dispatch, ReactNode, SetStateAction, useState } from 'react';

import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { Button, Dialog, Paper, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import {
  labelClose,
  labelEditAnomalyDetectionConfirmation,
  labelEditAnomalyDetectionClosing,
  labelSave,
  labelConfirm,
} from '../../../translatedLabels';
import TimePeriodButtonGroup from '../TimePeriods';

import AnomalyDetectionExclusionPeriod from './AnomalyDetectionExclusionPeriod';
import AnomalyDetectionModalConfirmation from './AnomalyDetectionModalConfirmation';
import AnomalyDetectionSlider from './AnomalyDetectionSlider';
import { CustomFactorsData } from './models';
import { countedRedCirclesAtom } from './anomalyDetectionAtom';

const useStyles = makeStyles((theme) => ({
  close: {
    display: 'flex',
    justifyContent: 'flex-end',
  },
  container: {
    '& .MuiDialog-paper': {
      backgroundColor: theme.palette.background.default,
      maxWidth: '80%',
      padding: theme.spacing(2),
      width: '100%',
    },
  },
  editEnvelopeSize: {
    display: 'flex',
    justifyContent: 'space-between',
    width: '30%',
  },
  envelopeSize: {
    flex: 1,
    marginRight: theme.spacing(1),
  },
  exclusionPeriod: {
    flex: 2,
    height: theme.spacing(20),
    marginLeft: theme.spacing(1),
  },
  spacing: {
    paddingBottom: theme.spacing(1),
  },
}));

interface PropsChildren {
  factorsData?: CustomFactorsData | null;
  getFactors?: (data: CustomFactorsData) => void;
  isEnvelopeResizingCanceled?: boolean;
  isResizeEnvelope?: boolean;
  openModalConfirmation?: (value: boolean) => void;
  setIsResizeEnvelope?: Dispatch<SetStateAction<boolean>>;
}

interface Props {
  children: (args: PropsChildren) => ReactNode;
  isOpen: boolean;
  setIsOpen: Dispatch<SetStateAction<boolean>>;
}

const EditAnomalyDetectionDataDialog = ({
  isOpen,
  setIsOpen,
  children,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const [factorsData, setFactorsData] = useState<null | CustomFactorsData>(
    null,
  );
  const [isModalConfirmationOpened, setIsModalConfirmationOpened] =
    useState(false);

  const [isEnvelopeResizingCanceled, setIsEnvelopeResizingCanceled] =
    useState(false);

  const [isResizeEnvelope, setIsResizeEnvelope] = useState(false);
  const [
    isModalEditAnomalyDetectionConfirmationOpened,
    setIsModalEditAnomalyDetectionConfirmationOpened,
  ] = useState(false);
  const setCountedRedCircles = useUpdateAtom(countedRedCirclesAtom);

  const handleClose = (): void => {
    if (!factorsData?.isResizing) {
      setIsOpen(false);
      setCountedRedCircles(null);

      return;
    }
    setIsModalEditAnomalyDetectionConfirmationOpened(true);
  };

  const getFactors = (data: CustomFactorsData): void => {
    setFactorsData(data);
  };

  const openModalConfirmation = (value: boolean): void => {
    setIsModalConfirmationOpened(value);
    setIsEnvelopeResizingCanceled(false);
  };
  const cancelResizeEnvelope = (value: boolean): void => {
    setIsEnvelopeResizingCanceled(value);
  };

  const resizeEnvelope = (value: boolean): void => {
    setIsResizeEnvelope(value);
    setIsModalConfirmationOpened(false);
  };

  const closeModal = (): void => {
    setIsOpen(false);
    setCountedRedCircles(null);
  };

  return (
    <Dialog className={classes.container} open={isOpen} onClose={handleClose}>
      <div>
        <div className={classes.spacing}>
          <TimePeriodButtonGroup />
        </div>
        <div className={classes.spacing}>{children?.({ factorsData })}</div>
        <div className={classes.editEnvelopeSize}>
          <Paper className={classes.envelopeSize}>
            {children?.({
              getFactors,
              isEnvelopeResizingCanceled,
              isResizeEnvelope,
              openModalConfirmation,
              setIsResizeEnvelope,
            })}
          </Paper>
        </div>
        <EditAnomalyDetectionDataDialog.ModalConfirmation
          labelConfirm={labelSave}
          open={isModalConfirmationOpened}
          sendCancel={cancelResizeEnvelope}
          sendConfirm={resizeEnvelope}
          setOpen={setIsModalConfirmationOpened}
        >
          <Typography> {t(labelEditAnomalyDetectionConfirmation)} </Typography>
        </EditAnomalyDetectionDataDialog.ModalConfirmation>

        <EditAnomalyDetectionDataDialog.ModalClosing
          labelConfirm={labelConfirm}
          open={isModalEditAnomalyDetectionConfirmationOpened}
          sendCancel={(): void =>
            setIsModalEditAnomalyDetectionConfirmationOpened(true)
          }
          sendConfirm={closeModal}
          setOpen={setIsModalEditAnomalyDetectionConfirmationOpened}
        >
          {t(labelEditAnomalyDetectionClosing)}
        </EditAnomalyDetectionDataDialog.ModalClosing>
        <div className={classes.close}>
          <Button onClick={handleClose}>{t(labelClose)}</Button>
        </div>
      </div>
    </Dialog>
  );
};

EditAnomalyDetectionDataDialog.Slider = AnomalyDetectionSlider;
EditAnomalyDetectionDataDialog.ExclusionPeriod =
  AnomalyDetectionExclusionPeriod;
EditAnomalyDetectionDataDialog.ModalConfirmation =
  AnomalyDetectionModalConfirmation;
EditAnomalyDetectionDataDialog.ModalClosing = AnomalyDetectionModalConfirmation;

export default EditAnomalyDetectionDataDialog;
