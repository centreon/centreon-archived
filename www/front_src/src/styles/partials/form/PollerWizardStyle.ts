import makeStyles from '@mui/styles/makeStyles';
import { CreateCSSProperties } from '@mui/styles';
import { Theme } from '@mui/material';

interface StylesProps {
  statusCreating: boolean | null;
  statusGenerating: boolean | null;
}

const useStyles = makeStyles<Theme>((theme) => ({
  formButton: {
    columnGap: theme.spacing(1),
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'flex-end',
    marginTop: theme.spacing(1.875),
  },
  formHeading: {
    marginBottom: theme.spacing(0.625),
  },
  formItem: {
    paddingBottom: theme.spacing(1.875),
  },
  formText: {
    color: '#242f3a',
    fontFamily: 'Roboto Regular',
    fontSize: theme.spacing(1.5),
    margin: '20px 0',
  },
}));

const useStylesWithProps = makeStyles<Theme, StylesProps>((theme) => ({
  formButton: {
    columnGap: theme.spacing(1),
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'flex-end',
    marginTop: theme.spacing(1.875),
  },
  formHeading: {
    marginBottom: theme.spacing(0.625),
  },
  statusCreating: ({ statusCreating }): CreateCSSProperties<StylesProps> => ({
    color: statusCreating ? '#acd174' : '#d0021b',
  }),
  statusGenerating: ({
    statusGenerating,
  }): CreateCSSProperties<StylesProps> => ({
    color: statusGenerating ? '#acd174' : '#d0021b',
  }),
}));

export { useStyles, useStylesWithProps };
