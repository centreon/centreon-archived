import { Dispatch, SetStateAction, useEffect, useState } from 'react';

import { useAtom } from 'jotai';
import { equals, path } from 'ramda';
import { useTranslation } from 'react-i18next';

import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';
import ResetIcon from '@mui/icons-material/SettingsBackupRestore';
import { Button, Divider, Slider, Tooltip, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { IconButton, putData, useRequest } from '@centreon/ui';

import { ResourceDetails, Sensitivity } from '../../../Details/models';
import {
  labelCancel,
  labelMenageEnvelope,
  labelMenageEnvelopeSubTitle,
  labelPointsOutsideOfEnvelopeCount,
  labelSave,
  labelResetToDefaultValue,
} from '../../../translatedLabels';

import { countedRedCirclesAtom } from './anomalyDetectionAtom';
import { CustomFactorsData } from './models';

const useStyles = makeStyles((theme) => ({
  body: {
    display: 'flex',
    flexDirection: 'column',
  },
  bodyContainer: {
    alignItems: 'center',
    display: 'flex',
    marginBottom: theme.spacing(2),
    marginTop: theme.spacing(5),
  },
  confirmButton: {
    marginLeft: theme.spacing(2),
  },
  container: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-evenly',
    padding: theme.spacing(2),
  },
  defaultButton: {
    justifyContent: 'flex-start',
    textTransform: 'none',
  },
  divider: {
    margin: theme.spacing(0.5, 0, 2, 0),
  },
  footer: {
    display: 'flex',
    justifyContent: 'space-between',
  },
  header: {
    display: 'flex',
    flexDirection: 'column',
  },
  icon: {
    display: 'flex',
    flexDirection: 'column',
  },
  slider: {
    '& .MuiSlider-mark': {
      borderLeft: '1px solid',
      height: theme.spacing(2),
      width: 0,
    },
    '& .MuiSlider-thumb': {
      height: theme.spacing(3),
      width: 1,
    },
    '& .MuiSlider-valueLabel': {
      backgroundColor: theme.palette.primary.main,
      borderRadius: '50%',
      height: theme.spacing(2.25),
      width: theme.spacing(1.25),
    },
    '& .MuiSlider-valueLabel:before': {
      width: 0,
    },
    '& .MuiSlider-valueLabelOpen': {
      transform: 'translateY(-60%) scale(1)',
    },
    display: 'flex',
    justifyContent: 'space-evenly',
    width: theme.spacing(35),
  },
}));

interface Props {
  details: ResourceDetails;
  isEnvelopeResizingCanceled?: boolean;
  isResizeEnvelope?: boolean;
  openModalConfirmation?: (value: boolean) => void;
  sendFactors: (data: CustomFactorsData) => void;
  sendReloadGraphPerformance: (value: boolean) => void;
  sensitivity: Sensitivity;
  setIsResizeEnvelope?: Dispatch<SetStateAction<boolean>>;
}

const AnomalyDetectionSlider = ({
  sendFactors,
  sensitivity,
  details,
  openModalConfirmation,
  isEnvelopeResizingCanceled,
  isResizeEnvelope,
  sendReloadGraphPerformance,
  setIsResizeEnvelope,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const [currentValue, setCurrentValue] = useState(sensitivity.current_value);
  const [isDefaultValue, setIsDefaultValue] = useState(false);
  const [isResizingConfirmed, setIsResizingConfirmed] = useState(false);
  const [openTooltip, setOpenTooltip] = useState(false);
  const { sendRequest } = useRequest({
    request: putData,
  });
  const [countedRedCircles, setCountedRedCircles] = useAtom(
    countedRedCirclesAtom,
  );

  const tooltipMessage = `${countedRedCircles} ${t(
    labelPointsOutsideOfEnvelopeCount,
  )}`;

  const step = 0.1;
  const sensitivityEndPoint = path<string>(
    ['links', 'endpoints', 'sensitivity'],
    details,
  );

  const marks = [
    {
      label: 'Default',
      value: sensitivity.default_value,
    },
  ];

  const isEnvelopeUpdateSliderEnabled = (): void => {
    setIsDefaultValue(false);
    setIsResizingConfirmed(true);
  };

  const handleChangeSlider = (event): void => {
    setCurrentValue(event.target.value);
    isEnvelopeUpdateSliderEnabled();
    setOpenTooltip(true);
  };

  const handleAdd = (): void => {
    const newCurrentValue = Number((step + currentValue).toFixed(1));
    setCurrentValue(newCurrentValue);
    isEnvelopeUpdateSliderEnabled();
    setOpenTooltip(true);
  };

  const handleRemove = (): void => {
    const newCurrentValue = Number((currentValue - step).toFixed(1));
    setCurrentValue(newCurrentValue);
    isEnvelopeUpdateSliderEnabled();
    setOpenTooltip(true);
  };

  const setToDefaultValue = (): void => {
    setIsResizingConfirmed(true);
    if (isDefaultValue) {
      return;
    }
    setIsDefaultValue(true);
    setOpenTooltip(true);
  };

  const confirm = (): void => {
    if (!openModalConfirmation) {
      return;
    }
    openModalConfirmation(true);
    setCountedRedCircles(null);
  };

  const resizeEnvelope = (): void => {
    sendRequest({
      data: { sensitivity: currentValue },
      endpoint: sensitivityEndPoint,
    });

    sendReloadGraphPerformance(true);
    setIsResizingConfirmed(false);
  };

  const cancelResizingEnvelope = (): void => {
    setCurrentValue(sensitivity.current_value);
    setIsResizingConfirmed(false);
    setIsDefaultValue(false);
    setCountedRedCircles(null);
  };

  useEffect(() => {
    if (openTooltip) {
      setTimeout(() => {
        setOpenTooltip(false);
      }, 1000);
    }
  }, [openTooltip]);

  useEffect(() => {
    if (isDefaultValue) {
      setCurrentValue(sensitivity.default_value);
    }
  }, [isDefaultValue]);

  useEffect(() => {
    if (
      equals(currentValue, sensitivity.default_value) &&
      isResizingConfirmed
    ) {
      setIsDefaultValue(true);
    }
    if (isResizeEnvelope && setIsResizeEnvelope) {
      setIsResizeEnvelope(false);
      sendReloadGraphPerformance(false);
    }

    sendFactors({
      currentFactor: sensitivity.current_value,
      isResizing: isResizingConfirmed,
      simulatedFactor: currentValue,
    });
  }, [currentValue, isResizingConfirmed]);

  useEffect(() => {
    if (isEnvelopeResizingCanceled) {
      cancelResizingEnvelope();
    }
  }, [isEnvelopeResizingCanceled]);

  useEffect(() => {
    if (isResizeEnvelope) {
      resizeEnvelope();
    }
  }, [isResizeEnvelope]);

  return (
    <div className={classes.container}>
      <div className={classes.header}>
        <Typography variant="h6">{t(labelMenageEnvelope)}</Typography>
        <Tooltip
          open={countedRedCircles ? openTooltip : false}
          title={tooltipMessage}
        >
          <div />
        </Tooltip>
        <Typography variant="caption">
          {t(labelMenageEnvelopeSubTitle)}
        </Typography>
      </div>

      <div className={classes.body}>
        <div className={classes.bodyContainer}>
          <IconButton data-testid="remove" size="small" onClick={handleRemove}>
            <div className={classes.icon}>
              <RemoveIcon fontSize="small" />
              <Typography variant="subtitle2">
                {sensitivity.minimum_value}
              </Typography>
            </div>
          </IconButton>

          <Slider
            aria-label="Small"
            className={classes.slider}
            marks={marks}
            max={sensitivity.maximum_value}
            min={sensitivity.minimum_value}
            size="small"
            step={step}
            value={currentValue}
            valueLabelDisplay="on"
            onChange={handleChangeSlider}
          />
          <IconButton data-testid="add" size="small" onClick={handleAdd}>
            <div className={classes.icon}>
              <AddIcon fontSize="small" />
              <Typography variant="subtitle2">
                {sensitivity.maximum_value}
              </Typography>
            </div>
          </IconButton>
        </div>
        <Button
          className={classes.defaultButton}
          disabled={isDefaultValue}
          startIcon={<ResetIcon />}
          onClick={setToDefaultValue}
        >
          {t(labelResetToDefaultValue)}
        </Button>
      </div>

      <Divider className={classes.divider} />

      <div className={classes.footer}>
        <Button
          size="small"
          variant="outlined"
          onClick={cancelResizingEnvelope}
        >
          {t(labelCancel)}
        </Button>
        <Button
          className={classes.confirmButton}
          disabled={!isResizingConfirmed}
          size="small"
          variant="contained"
          onClick={confirm}
        >
          {t(labelSave)}
        </Button>
      </div>
    </div>
  );
};

export default AnomalyDetectionSlider;
