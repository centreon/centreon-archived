import { Dispatch, ReactNode, SetStateAction, useState } from 'react';

import { useUpdateAtom } from 'jotai/utils';

import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import Paper from '@mui/material/Paper';
import makeStyles from '@mui/styles/makeStyles';

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
    width: '50%',
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
  isCanceledResizeEnvelope?: boolean;
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

  const [factorsData, setFactorsData] = useState<null | CustomFactorsData>(
    null,
  );
  const [isModalConfirmationOpened, setIsModalConfirmationOpened] =
    useState(false);

  const [isCanceledResizeEnvelope, setIsCanceledResizeEnvelope] =
    useState(false);

  const [isResizeEnvelope, setIsResizeEnvelope] = useState(false);
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
    setIsCanceledResizeEnvelope(false);
  };
  const cancelResizeEnvelope = (value: boolean): void => {
    setIsCanceledResizeEnvelope(value);
  };

  const resizeEnvelope = (value: boolean): void => {
    setIsResizeEnvelope(value);
    setIsModalConfirmationOpened(false);
  };

  return (
    <Dialog className={classes.container} open={isOpen}>
      <div>
        <div className={classes.spacing}>
          <TimePeriodButtonGroup />
        </div>
        <div className={classes.spacing}>
          {children && children({ factorsData })}
        </div>
        <div className={classes.editEnvelopeSize}>
          <Paper className={classes.envelopeSize}>
            {children &&
              children({
                getFactors,
                isCanceledResizeEnvelope,
                isResizeEnvelope,
                openModalConfirmation,
                setIsResizeEnvelope,
              })}
          </Paper>
        </div>
        <EditAnomalyDetectionDataDialog.ModalConfirmation
          open={isModalConfirmationOpened}
          sendCancel={cancelResizeEnvelope}
          sendConfirm={resizeEnvelope}
          setOpen={setIsModalConfirmationOpened}
        />
        <div className={classes.close}>
          <Button onClick={handleClose}>Close</Button>
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

export default EditAnomalyDetectionDataDialog;
