export default {
    moduleFileExtensions: [
        'js',
        'jsx',
        'json',
        'vue'
    ],
    transform: {
        '^.+\\.vue$': '@vue/vue3-jest',
        '^.+\\.(js|jsx)?$': 'babel-jest'
    },
    moduleNameMapper: {
        '^@/(.*)$': '<rootDir>/resources/js/$1'
    },
    testEnvironment: 'jsdom',
    testMatch: [
        '<rootDir>/tests/**/*.test.js',
        '<rootDir>/tests/**/*.spec.js'
    ],
    transformIgnorePatterns: ['/node_modules/'],
    setupFiles: ['<rootDir>/tests/setup.js']
}; 