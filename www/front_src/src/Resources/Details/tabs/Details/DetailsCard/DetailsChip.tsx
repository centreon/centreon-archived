import { useState } from 'react';

import { useTranslation } from 'react-i18next';
import clsx from 'clsx';

import makeStyles from '@mui/styles/makeStyles';
import SettingsIcon from '@mui/icons-material/Settings';
import FilterListIcon from '@mui/icons-material/FilterList';
import IconButton from '@mui/material/IconButton';
import { Chip, Grid, Tooltip, Typography } from '@mui/material';

const useStyles = makeStyles((theme) => ({
  chip: {
    alignSelf: 'center',
    display: 'flex',
  },
  chipAction: {
    gridArea: '1/1',
    maxWidth: theme.spacing(14),
    minWidth: theme.spacing(8),
    overflow: 'hidden',
  },
  chipHover: {
    backgroundColor: theme.palette.primary.main,
    display: 'flex',
    gap: theme.spacing(0.25),
    gridArea: '1/1',
  },
  chipIconColor: {
    color: theme.palette.common.white,
  },
  chipLabel: {
    display: 'grid',
    justifyItems: 'center',
    minWidth: theme.spacing(7),
    overflow: 'hidden',
  },
  chipLabelColor: { color: 'transparent' },
}));

interface Props {
  goToConfiguration: () => void;
  id: number;
  name: string;
  setFilter: () => void;
}

const DetailsChip = ({
  name,
  setFilter,
  id,
  goToConfiguration,
}: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();
  const [isHovered, setIsHovered] = useState<boolean>(false);

  const mouseEnter = (): void => {
    setIsHovered(true);
  };

  const mouseLeave = (): void => {
    setIsHovered(false);
  };

  return (
    <Grid item className={classes.chip} key={id}>
      <Chip
        aria-label={`${name} Chip`}
        color="primary"
        label={
          <div className={classes.chipLabel}>
            <Tooltip title={name}>
              <Typography
                className={clsx(
                  classes.chipAction,
                  isHovered ? classes.chipLabelColor : '',
                )}
                variant="body2"
              >
                {name}
              </Typography>
            </Tooltip>
            {isHovered && (
              <Grid className={classes.chipHover}>
                <IconButton
                  aria-label={`${name} Filter`}
                  className={classes.chipIconColor}
                  size="small"
                  title={t(name)}
                  onClick={setFilter}
                >
                  <FilterListIcon fontSize="small" />
                </IconButton>
                <IconButton
                  aria-label={`${name} Configure`}
                  className={classes.chipIconColor}
                  size="small"
                  title={t(name)}
                  onClick={goToConfiguration}
                >
                  <SettingsIcon fontSize="small" />
                </IconButton>
              </Grid>
            )}
          </div>
        }
        onMouseEnter={mouseEnter}
        onMouseLeave={mouseLeave}
      />
    </Grid>
  );
};

export default DetailsChip;
