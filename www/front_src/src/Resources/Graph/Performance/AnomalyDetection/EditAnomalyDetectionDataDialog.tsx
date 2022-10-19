import { Dispatch, ReactNode, SetStateAction, useState } from 'react';

import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { Button, Dialog, Paper } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { labelClose } from '../../../translatedLabels';
import TimePeriodButtonGroup from '../TimePeriods';

import AnomalyDetectionExclusionPeriod from './AnomalyDetectionExclusionPeriod';
import AnomalyDetectionModalConfirmation from './AnomalyDetectionModalConfirmation';
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

interface PropsGraph {
  factorsData?: CustomFactorsData | null;
}
interface PropsSlider {
  getFactors: (data: CustomFactorsData) => void;
  isEnvelopeResizingCanceled: boolean;
  isResizingEnvelope: boolean;
  openModalConfirmation: (value: boolean) => void;
  setIsResizingEnvelope: Dispatch<SetStateAction<boolean>>;
}

interface Props {
  isOpen: boolean;
  renderGraph: (args: PropsGraph) => ReactNode;
  renderSlider: (args: PropsSlider) => ReactNode;
  setIsOpen: Dispatch<SetStateAction<boolean>>;
}

const EditAnomalyDetectionDataDialog = ({
  isOpen,
  setIsOpen,
  renderGraph,
  renderSlider,
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

  const [isResizingEnvelope, setIsResizingEnvelope] = useState(false);
  const setCountedRedCircles = useUpdateAtom(countedRedCirclesAtom);

  const handleClose = (): void => {
    setIsOpen(false);
    setCountedRedCircles(null);
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
    setIsResizingEnvelope(value);
    setIsModalConfirmationOpened(false);
  };

  return (
    <Dialog
      className={classes.container}
      data-testid="modalEditAnomalyDetection"
      open={isOpen}
    >
      <div>
        <div className={classes.spacing}>
          <TimePeriodButtonGroup />
        </div>
        <div className={classes.spacing}>{renderGraph?.({ factorsData })}</div>
        <div className={classes.editEnvelopeSize}>
          <Paper className={classes.envelopeSize}>
            {renderSlider?.({
              getFactors,
              isEnvelopeResizingCanceled,
              isResizingEnvelope,
              openModalConfirmation,
              setIsResizingEnvelope,
            })}
          </Paper>
        </div>
        <EditAnomalyDetectionDataDialog.ModalConfirmation
          dataTestid="modalConfirmation"
          open={isModalConfirmationOpened}
          sendCancel={cancelResizeEnvelope}
          sendConfirm={resizeEnvelope}
          setOpen={setIsModalConfirmationOpened}
        />
        <div className={classes.close}>
          <Button data-testid="closeEditModal" onClick={handleClose}>
            {t(labelClose)}
          </Button>
        </div>
      </div>
    </Dialog>
  );
};

EditAnomalyDetectionDataDialog.ExclusionPeriod =
  AnomalyDetectionExclusionPeriod;
EditAnomalyDetectionDataDialog.ModalConfirmation =
  AnomalyDetectionModalConfirmation;

export default EditAnomalyDetectionDataDialog;
